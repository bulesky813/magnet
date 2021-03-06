<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 600,
        'processes' => 1,
        'concurrent' => [
            'limit' => 5,
        ],
    ],
    'classify' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'channel.queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 3600,
        'processes' => 1,
        'concurrent' => [
            'limit' => 5,
        ],
    ],
];
