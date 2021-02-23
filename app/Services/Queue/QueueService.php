<?php

namespace App\Services\Queue;

use App\Model\Number;
use App\Model\Subject;
use App\Services\Jav\JavDbService;
use App\Services\Number\NumberService;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Codec\Json;

class QueueService
{
    /**
     * @AsyncQueueMessage
     */
    public function subject($params)
    {
        $url = Arr::get($params, 'url', '');
        try {
            /*$db_number = Number::query()->where('number', $number)->first();
            if (!$db_number) {
                Number::query()->insert([
                    'number' => $number,
                ]);
            }*/
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
            echo $e->getTraceAsString() . PHP_EOL;
        }
    }
}
