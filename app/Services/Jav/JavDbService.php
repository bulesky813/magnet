<?php

namespace App\Services\Jav;

use App\Services\Base\GuzzleService;
use DiDom\Document;
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

    public function __construct(string $url)
    {
        $this->uriChange($url);
    }

    public function uriChange($url): JavDbService
    {
        $this->url = $url;
        $this->uri = parse_url($url);
        return $this;
    }

    public function spider(): JavDbService
    {

        try {
            $response = $this->gs->create([
                'base_uri' => sprintf('%s://%s', $this->uri['scheme'], $this->uri['host']),
                'verify' => false
            ])->request('GET', sprintf("%s?%s", $this->uri['path'], Arr::get($this->uri, 'query', '')), [
                'cookies' => $this->cookies(),
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
                ]
            ]);
        } catch (\Throwable $e) {
            throw $e;
        }
        $this->contents = $response->getBody()->getContents();
        return $this;
    }

    public function search()
    {
        $host_keys = str_replace(['www.', '.com'], [''], $this->uri['host']);
        $dom = new Document($this->contents);
        $genres_arr = [];
        $alt_arr = $dom->find(config(sprintf("spider.%s.search.alt", $host_keys)), Query::TYPE_XPATH);
        $images_arr = $dom->find(config(sprintf("spider.%s.search.images", $host_keys)), Query::TYPE_XPATH);
        $directors_arr = [];
        if (config(sprintf("spider.%s.search.directors", $host_keys), '')) {
            $directors_arr = $dom->find(config(sprintf("spider.%s.search.directors", $host_keys)), Query::TYPE_XPATH);
        }
        $title_arr = $dom->find(config(sprintf("spider.%s.search.title", $host_keys)), Query::TYPE_XPATH);
        if (config(sprintf("spider.%s.search.year", $host_keys), '')) {
            $year = $dom->find(config(sprintf("spider.%s.search.year", $host_keys)), Query::TYPE_XPATH);
        }
        $list = [];
        foreach ($title_arr as $key => $value) {
            $list[] = [
                'genres' => $genres_arr,
                'alt' => strpos($alt_arr[$key], 'http') === false ?
                    sprintf('%s://%s', $this->uri['scheme'], $this->uri['host']) . $alt_arr[$key] : $alt_arr[$key],
                'directors' => [Arr::get($directors_arr, $key, '')],
                'title' => $title_arr[$key],
                'year' => $year ?? '',
                'images' => $images_arr[$key]
            ];
        }
        return $list;
    }

    public function subject()
    {
        $dom = new Document($this->contents);
        $genres = $dom->find('//div[@class="detail_data"]/table[2]/tr[9]/td/a/text()', Query::TYPE_XPATH);
        $images_medium = $dom->find('//div[@class="detail_photo"]/h2/img/@src', Query::TYPE_XPATH);
        $title = $dom->find('/html/body/div[2]/article[2]/div[1]/h1/text()', Query::TYPE_XPATH);
        $rating = $dom->find('//td[@class="review"]/text()', Query::TYPE_XPATH);
        $casts_arr = $dom->find('//div[@class="detail_data"]/table[2]/tr[1]/td/a', Query::TYPE_XPATH);
        return [
            'genres' => collect($genres)->map(function ($genre, $key) {
                return trim($genre);
            }),
            'images_medium' => Arr::get($images_medium, 0, ''),
            'images_large' => str_replace('_o1_', '_e_', Arr::get($images_medium, 0, '')),
            'original_title' => $title,
            'alt' => $this->url,
            'title' => Arr::get($title, 0, ''),
            'rating' => trim(implode("", $rating)),
            'casts' => collect($casts_arr)->map(function ($cast, $key) {
                return [
                    'url' => sprintf('%s://%s', $this->uri['scheme'], $this->uri['host']) . $cast->getAttribute("href"),
                    'name' => $cast->text()
                ];
            })->toArray()
        ];
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
            'bWdzdGFnZS5jb20%3D-_lr_hb_-r2icil%2Fmgs' => '{%22heartbeat%22:1611593361686}'
        ], '.mgstage.com');
    }
}
