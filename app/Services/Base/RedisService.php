<?php

namespace App\Services\Base;

use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

trait RedisService
{
    public function redis()
    {
        $container = ApplicationContext::getContainer();
        return $container->get(Redis::class);
    }
}
