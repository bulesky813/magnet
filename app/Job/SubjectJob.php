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
            $force = Arr::get($this->params, 'force', false); //强制刷新
            if (!$force) {
                $subject = Subject::query()->where('source', $url)->first();
                if ($subject) {
                    return true;
                }
            }
            $content = make(JavDbService::class, [$url])->spider(10)->subject();
            $number = Arr::get($content, 'number', '');
            $favorites = Arr::get($content, 'favorites', 0);
            if (!$number) {
                return true;
            }
            $subject = Subject::query()->where('number', $number)->first();
            if (!$subject || $force == true) {
                if (!$subject) {
                    $subject = make(Subject::class);
                    $subject->number = $number;
                }
                $subject->content = $content;
                $subject->source = $url;
                $subject->favorites = intval($favorites);
                $subject->save();
            }
            collect(Arr::get($content, 'casts', []))->each(function ($casts, $key) use ($number) {
                $casts_name = strtoupper(trim(Arr::get($casts, 'name', '')));
                $casts_url = Arr::get($casts, 'url', '');
                if (filter_var($casts_url, FILTER_VALIDATE_URL) !== false) {
                    $mCasts = Casts::query()->where('casts', $casts_name)->first();
                    if (!$mCasts) {
                        $mCasts = make(Casts::class);
                        $mCasts->casts = $casts_name;
                        $mCasts->works = [$number];
                        $mCasts->url = $casts_url;
                        $mCasts->save();
                    } else {
                        Casts::query()->where('casts', $casts_name)
                            ->whereRaw("ISNULL(JSON_SEARCH(works, 'one', '{$number}'))")
                            ->update([
                                'works' => Db::raw("JSON_ARRAY_APPEND(works, '$', '$number')")
                            ]);
                    }
                }
            });
            return true;
        } catch (\Throwable $e) {
            echo $e->getTraceAsString() . PHP_EOL;
            throw $e;
        }
    }
}
