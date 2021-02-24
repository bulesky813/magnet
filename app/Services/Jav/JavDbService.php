<?php

namespace App\Services\Jav;

use App\Services\Base\GuzzleService;
use Carbon\Carbon;
use DiDom\Document;
use DiDom\Element;
use DiDom\Query;
use GuzzleHttp\Cookie\CookieJar;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;

class JavDbService
{
    protected $contents = '';
    protected $url;
    /**
     * @Inject
     * @var GuzzleService
     */
    protected $gs;
    protected $uri = [];
    protected $rule_keys = '';

    public function __construct(string $url)
    {
        $this->uriChange($url);
    }

    public function uriChange($url): JavDbService
    {
        $this->url = $url;
        $this->uri = parse_url($url);
        $this->rule_keys = sprintf("spider.%s", str_replace(['www.', '.com', '.co.jp'], [''], $this->uri['host']));
        return $this;
    }

    public function spider(): JavDbService
    {
        try {
            $response = $this->gs->create([
                'base_uri' => $this->uriPretreatment(''),
                'verify' => false
            ])->request(
                'GET',
                sprintf("%s?%s", Arr::get($this->uri, 'path', ''), Arr::get($this->uri, 'query', '')),
                [
                    'cookies' => $this->cookies(),
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
                    ]
                ]
            );
        } catch (\Throwable $e) {
            return $this;
        }
        $this->contents = $response->getBody()->getContents();
        return $this;
    }

    public function search()
    {
        $dom = new Document($this->contents);
        $search_xpath = ['genres', 'alt', 'images', 'directors', 'title', 'year'];
        list($genres, $alt, $images, $directors, $title, $year) = collect($search_xpath)
            ->map(function ($rule_name, $key) use ($dom) {
                if (config(sprintf("%s.search.%s", $this->rule_keys, $rule_name))) {
                    return $dom->find(
                        config(sprintf("%s.search.%s", $this->rule_keys, $rule_name)),
                        Query::TYPE_XPATH
                    );
                }
                return [];
            });
        $list = [];
        foreach ($title as $key => $value) {
            $list[] = [
                'genres' => $genres,
                'alt' => $this->uriPretreatment(Arr::get($alt, $key, '')),
                'directors' => Arr::get($directors, $key, '')
                    ? [Arr::get($directors, $key, '')] : [],
                'title' => Arr::get($title, $key, ''),
                'year' => Arr::get($year, $key, '') ?
                    Carbon::parse(Arr::get($year, $key, ''))->year : '',
                'images' => $this->uriPretreatment(Arr::get($images, $key, ''))
            ];
        }
        return $list;
    }

    public function subject()
    {
        $dom = new Document($this->contents);
        $subject_xpath = [
            'genres',
            'images_medium',
            'title',
            'rating',
            'casts',
            'year',
            'summary',
            'number',
            'images_content',
            'favorites'
        ];
        list($genres, $images_medium, $title, $rating, $casts, $year, $summary, $number, $images_content, $favorites) = collect($subject_xpath)
            ->map(function ($xpath_name, $key) use ($dom) {
                $type_xpath = config(sprintf("%s.subject.%s", $this->rule_keys, $xpath_name));
                if ($type_xpath) {
                    if (strpos($type_xpath, '|') === false) {
                        return $dom->find($type_xpath, Query::TYPE_XPATH);
                    } else {
                        $elements = [];
                        collect(explode("|", $type_xpath))->each(function ($child_xpath, $key) use ($dom, &$elements) {
                            $elements = $dom->find($child_xpath, Query::TYPE_XPATH);
                            if (count($elements) > 0) {
                                return false;
                            }
                        });
                        return $elements;
                    }
                }
                return [];
            });
        $number = trim(Arr::get($number, 0, ''));
        $other_casts = $this->findAvHelperCasts($number);
        return [
            'genres' => collect($genres)->map(function ($genre, $key) {
                return trim($genre);
            }),
            'images_medium' => Arr::get($images_medium, 0, ''),
            'images_large' => str_replace('_o1_', '_e_', Arr::get($images_medium, 0, '')),
            'original_title' => collect($title)->map(function ($t, $k) {
                return trim($t);
            }),
            'alt' => $this->url,
            'summary' => trim(Arr::get($summary, 0, '')),
            'title' => trim(Arr::get($title, 0, '')),
            'rating' => trim(implode("", $rating)),
            'year' => trim(Arr::get($year, 0, ''))
                ? Carbon::parse(Arr::get($year, 0, ''))->format('Y/m/d') : '',
            'casts' => $other_casts ? [$other_casts] : collect($casts)->map(function ($cast, $key) {
                return [
                    'url' => $cast instanceof Element ? $this->uriPretreatment($cast->getAttribute("href")) : '',
                    'name' => trim($cast instanceof Element ? $cast->text() : $cast)
                ];
            })->toArray(),
            'number' => $number,
            'images_content' => $images_content,
            'favorites' => str_replace([','], [""], trim(Arr::get($favorites, 0, '')))
        ];
    }

    public function hasNextPage(array $subjects): bool
    {
        switch ($this->rule_keys) {
            case 'spider.dmm':
                return count($subjects) == 121;
            default:
                return count($subjects) != 0;
        }
    }

    public function findAvHelperCasts(string $number): ?array
    {
        $response = $this->gs->create()->get("https://av-help.memo.wiki/search?keywords={$number}", [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
            ]
        ]);
        $contents = $response->getBody()->getContents();
        $dom = make(Document::class, [mb_convert_encoding($contents, 'utf-8', 'euc-jp')]);
        $elements = $dom->find('//div[@class="body"]/h3/a', Query::TYPE_XPATH);
        $execute_callback = [];
        do {
            $casts = array_pop($elements);
            $execute_callback[] = function () use ($casts) {
                try {
                    $response = $this->gs->create()->get($casts->getAttribute("href"), [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
                        ]
                    ]);
                    $contents = mb_convert_encoding($response->getBody()->getContents(), 'utf-8', 'euc-jp');
                    if (strpos($contents, '名前') !== false && strpos($contents, '生年月日') !== false) {
                        return [
                            'url' => $casts->getAttribute("href"),
                            'name' => $casts->text(),
                        ];
                    }
                    return false;
                } catch (\Throwable $e) {
                    return false;
                }
            };
            if (count($execute_callback) == 3 || count($elements) == 0) {
                $result = parallel($execute_callback);
                foreach ($result as $casts_result) {
                    if ($casts_result !== false) {
                        return $casts_result;
                    }
                }
                $execute_callback = [];
            }
        } while (count($elements) > 0);
        return [];
    }

    private function cookies(): CookieJar
    {
        return CookieJar::fromArray([
            'uuid' => 'ae212b02d0cdb7677bb5316782659b8a',
            '_ga' => 'GA1.2.1737840090.1602855000',
            '__ulfpc' => '202010162130013424',
            '_gid' => 'GA1.2.270176612.1611395791',
            'PHPSESSID' => '7fnqj3d40ne7g7o8r8utk65o17',
            'adc' => 1,
            'bWdzdGFnZS5jb20%3D-_lr_uf_-r2icil' => '3d18cfe9-6a62-4361-8ebf-e2ae1013545f',
            'bWdzdGFnZS5jb20%3D-_lr_tabs_-r2icil%2Fmgs' => '{%22sessionID%22:0%2C%22recordingID%22:%224-e4a35bbd-c8de-4a22-8b55-05e7458d512f%22%2C%22lastActivity%22:1611593241591}',
            'bWdzdGFnZS5jb20%3D-_lr_hb_-r2icil%2Fmgs' => '{%22heartbeat%22:1611593361686}',
            'age_check_done' => 1
        ], Arr::get($this->uri, 'host', ''));
    }

    private function uriPretreatment(string $url): string
    {
        return strpos($url, 'http') === false ?
            sprintf('%s://%s', $this->uri['scheme'], $this->uri['host']) . $url : $url;
    }
}
