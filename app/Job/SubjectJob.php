<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\Casts;
use App\Model\Subject;
use App\Services\Jav\JavDbService;
use Hyperf\AsyncQueue\Job;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use PhpParser\Node\Expr\Cast;

class SubjectJob extends Job
{
    private $params;
    protected $maxAttempts = 5;

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
            $content = make(JavDbService::class, [$url])->spider(10)->subject();
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
            collect(Arr::get($content, 'casts', []))->each(function ($casts, $key) use ($number) {
                $casts_name = strtoupper(trim(Arr::get($casts, 'name', '')));
                $casts_data = Casts::query()->where('casts', $casts_name)->first();
                if (!$casts_data) {
                    $mCasts = make(Casts::class);
                    $mCasts->casts = $casts_name;
                    $mCasts->works = [$number];
                    $mCasts->url = Arr::get($casts, 'url', '');
                    $mCasts->save();
                } else {
                    Casts::query()->where('casts', $casts_name)->update([
                        'works' => Db::raw("JSON_ARRAY_APPEND(works, '$', '$number')")
                    ]);
                }
            });
            return true;
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}
