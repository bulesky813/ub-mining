<?php

namespace App\Services\Base;

use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

trait BaseRedisService
{
    public function redis(): Redis
    {
        $container = ApplicationContext::getContainer();
        return $container->get(Redis::class);
    }
}
