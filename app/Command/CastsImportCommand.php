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
class CastsImportCommand extends HyperfCommand
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

        parent::__construct('cmd:casts_import');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('casts导入');
    }

    public function handle()
    {
        Subject::query()->orderBy("created_at", "desc")->chunk(1000, function ($subject_data, $key) {
            foreach ($subject_data as $subject) {
                collect($subject->content->casts)->each(function ($casts, $key) use ($subject) {
                    $casts_data = Casts::query()->where('casts', $casts->name)->first();
                    if (!$casts_data) {
                        $mCasts = make(Casts::class);
                        $mCasts->casts = $casts->name;
                        $mCasts->works = [$subject->number];
                        $mCasts->url = $casts->url;
                        $mCasts->save();
                    } else {
                        Casts::query()->where('casts', $casts->name)
                            ->whereRaw("ISNULL(JSON_SEARCH(works, 'one', '{$subject->number}'))")
                            ->update([
                                'works' => Db::raw("JSON_ARRAY_APPEND(works, '$', '{$subject->number}')")
                            ]);
                    }
                });
            }
        });
    }
}
