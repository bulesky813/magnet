<?php

namespace App\Services\Queue;

use App\Job\ClassifyJob;
use App\Job\SubjectJob;
use App\Services\Jav\JavDbService;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;

class QueueService
{
    public function subject($params)
    {
        $driver = ApplicationContext::getContainer()->get(DriverFactory::class)->get('default');
        $driver->push(new SubjectJob($params));
    }

    public function classify($params)
    {
        $driver = ApplicationContext::getContainer()->get(DriverFactory::class)->get('classify');
        $driver->push(new ClassifyJob($params));
    }
}
