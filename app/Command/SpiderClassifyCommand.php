<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Base\GuzzleService;
use App\Services\Base\RedisService;
use App\Services\Queue\QueueService;
use DiDom\Document;
use DiDom\Element;
use DiDom\Query;
use GuzzleHttp\Cookie\CookieJar;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use SebastianBergmann\CodeCoverage\Report\PHP;

/**
 * @Command
 */
class SpiderClassifyCommand extends HyperfCommand
{
    use RedisService;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var GuzzleService
     */
    protected $gs;

    protected $signature = 'cmd:spider_classify {type}';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('分类爬取');
    }

    public function handle()
    {
        $type = $this->input->getArgument("type");
        if ($type == 'mgstage') {
            $root_url = 'https://www.mgstage.com/ppv/genres.php';
            $response = $this->gs->create()->get($root_url, [
                'proxy' => env('PROXY', []) ?: [],
                'cookies' => CookieJar::fromArray([
                    'adc' => 1,
                    'age_check_done' => 1,
                ], 'mgstage.com'),
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
                ]
            ]);
            $contents = $response->getBody()->getContents();
            $dom = new Document($contents);
            $elements = $dom->find('//div[@id="genres_list"]/ul/li/a', Query::TYPE_XPATH);
            collect($elements)->each(function (Element $genre, $key) {
                $qs = make(QueueService::class);
                $qs->classify([
                    'url' => 'https://www.mgstage.com' . $genre->getAttribute("href") . '&page=%s'
                ]);
            });
        } elseif ($type == 'javbus') {
            $root_url = 'https://www.javbus.com/genre';
            $response = $this->gs->create()->get($root_url, [
                'proxy' => env('PROXY', []) ?: [],
                'cookies' => CookieJar::fromArray([
                    'adc' => 1,
                    'age_check_done' => 1,
                ], 'mgstage.com'),
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36 Edg/88.0.705.50'
                ]
            ]);
            $contents = $response->getBody()->getContents();
            $dom = new Document($contents);
            $elements = $dom->find('//div[@class="row genre-box"]/a', Query::TYPE_XPATH);
            collect($elements)->each(function (Element $genre, $key) {
                $qs = make(QueueService::class);
                $qs->classify([
                    'url' => $genre->getAttribute("href") . '/%s'
                ]);
            });
        }
    }
}
