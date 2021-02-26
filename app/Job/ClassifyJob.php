<?php

declare(strict_types=1);

namespace App\Job;

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
                $search_result = $jds->spider()->search();
                collect($search_result)->each(function ($subject, $key) {
                    $qs = make(QueueService::class);
                    $qs->subject([
                        'url' => Arr::get($subject, 'alt', '')
                    ]);
                });
                $page++;
                $jds->uriChange(sprintf($url, $page));
            } while ($jds->hasNextPage($search_result));
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
