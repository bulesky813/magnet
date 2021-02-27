<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\Subject;
use App\Services\Jav\JavDbService;
use App\Services\Queue\QueueService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;

class ClassifyJob extends Job
{
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {
            $url = Arr::get($this->params, 'url', '');
            $page = 1;
            $jds = make(JavDbService::class, [sprintf($url, $page)]);
            do {
                try {
                    $search_result = $jds->spider(10)->search();
                } catch (\Throwable $e) {
                    echo $e->getMessage() . PHP_EOL;
                    continue;
                }
                collect($search_result)->each(function ($subject, $key) {
                    $url = Arr::get($subject, 'alt', '');
                    $subject = Subject::query()->where('source', $url)->first();
                    if (!$subject) {
                        $qs = make(QueueService::class);
                        $qs->subject([
                            'url' => $url
                        ]);
                    }
                });
                $page++;
                $jds->uriChange(sprintf($url, $page));
            } while ($jds->hasNextPage($search_result));
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}
