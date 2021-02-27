<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\Subject;
use App\Services\Jav\JavDbService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;

class SubjectJob extends Job
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
            $subject = Subject::query()->where('source', $url)->first();
            if ($subject) {
                return true;
            }
            $content = make(JavDbService::class, [$url])->spider()->subject();
            $number = Arr::get($content, 'number', '');
            $favorites = Arr::get($content, 'favorites', 0);
            if (!$number) {
                return true;
            }
            make(Subject::class)->firstOrCreate(['number' => strtoupper($number)], [
                'number' => strtoupper($number),
                'content' => $content,
                'source' => $url,
                'favorites' => $favorites
            ]);
            return true;
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}
