<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Casts;
use App\Model\Number;
use App\Model\Subject;
use App\Services\Base\GuzzleService;
use App\Services\Base\RedisService;
use App\Services\Queue\QueueService;
use DiDom\Document;
use DiDom\Element;
use DiDom\Query;
use GuzzleHttp\Cookie\CookieJar;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use SebastianBergmann\CodeCoverage\Report\PHP;

/**
 * @Command
 */
class SubjectRetryCommand extends HyperfCommand
{
    use RedisService;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var QueueService
     */
    protected $qs;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('cmd:subject_retry');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('作品重新爬取');
    }

    public function handle()
    {
        Subject::query()
            ->whereRaw("!ISNULL(JSON_SEARCH(content->'$.casts[*].url', 'one', '')) OR !ISNULL(JSON_SEARCH(content->'$.casts[*].url', 'one', '%www.mgstage.com%'))")
            ->orderBy("number", "asc")
            ->chunk(100, function ($subject_data, $key) {
                foreach ($subject_data as $subject) {
                    $this->qs->subject([
                        'url' => $subject->source,
                        'force' => true
                    ]);
                }
            });
    }
}
