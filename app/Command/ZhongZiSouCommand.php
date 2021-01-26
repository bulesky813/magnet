<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Base\GuzzleService;
use App\Services\Base\RedisService;
use DiDom\Document;
use DiDom\Query;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use SebastianBergmann\CodeCoverage\Report\PHP;

/**
 * @Command
 */
class ZhongZiSouCommand extends HyperfCommand
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('cmd:zzs');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('种子搜爬虫');
    }

    public function handle()
    {
        $root_url = 'https://zhongziso88.xyz/list/300MIUM/%s';
        $page_num = 1;
        while (true) {
            try {
                $response = $this->gs->create()->get(sprintf($root_url, $page_num));
                if ($response->getStatusCode() != 200) {
                    throw new \Exception('http code error!', 404);
                }
            } catch (\Throwable $e) {
                echo $e->getCode() . PHP_EOL .
                    $e->getMessage() . PHP_EOL;
                continue;
            }
            $dom = new Document($response->getBody()->getContents());
            $work_names = $dom->find(
                "//*[@id=\"wrapp\"]/div[2]/div/div/div[2]/div/div[2]/table/tbody/tr/td/div/h4/a",
                Query::TYPE_XPATH
            );
            if (count($work_names) == 0) {
                echo 'not found any works!' . PHP_EOL;
                break;
            }
            $magnet_links = $dom->find(
                "//*[@id=\"wrapp\"]/div[2]/div/div/div[2]/div/div[2]/table/tbody/tr/td[4]/a",
                Query::TYPE_XPATH
            );
            foreach ($work_names as $key => $work) {
                preg_match("/300MIUM\-[0-9]{3}/", $work->text(), $matchs);
                if ($matchs) {
                    $work_name = Arr::get($matchs, 0, '');
                    $work_magnets = json_decode($this->redis()->hGet('300MIUM', $work_name) ?: "{}", true);
                    $work_magnets = array_flip($work_magnets);
                    $work_magnets[$magnet_links[$key]->attr("href")] = '';
                    $this->redis()->hSet('300MIUM', $work_name, json_encode(array_keys($work_magnets)));
                }
            }
            echo sprintf("page_num:%d completed", $page_num) . PHP_EOL;
            $page_num++;
        }
    }
}
