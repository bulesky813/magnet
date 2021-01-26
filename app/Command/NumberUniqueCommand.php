<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Bt;
use App\Model\Number;
use App\Services\Base\GuzzleService;
use App\Services\Base\RedisService;
use App\Services\Number\NumberService;
use DiDom\Document;
use DiDom\Query;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use SebastianBergmann\CodeCoverage\Report\PHP;
use \League\Flysystem\Filesystem;

/**
 * @Command
 */
class NumberUniqueCommand extends HyperfCommand
{
    use RedisService;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('cmd:number_unique');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('番号唯一性处理');
    }

    public function handle()
    {
        $parallel = new Parallel();
        $parallel->add(function () {
            $ns = make(NumberService::class);
            $ns->process('300MIUM', "/300MIUM\-[\s0-9]{3,}/i");
        });
        $parallel->add(function () {
            $ns = make(NumberService::class);
            $ns->process('300MAAN', "/300MAAN\-[\s0-9]{3,}/i");
        });
        $parallel->add(function () {
            $ns = make(NumberService::class);
            $ns->process('259LUXU', "/259LUXU\-[\s0-9]{3,}/i");
        });
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            echo $e->getMessage() . PHP_EOL;
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
