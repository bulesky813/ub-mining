<?php

declare(strict_types=1);

namespace App\Services\Queue;

use App\Job\ChildAssetsJob;
use App\Job\IncomeInfoJob;
use App\Job\PullOutJob;
use App\Job\UserInfoJob;
use App\Services\Base\BaseRedisService;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    use BaseRedisService;
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }


    public function childAssets($params, int $delay = 0): bool
    {
        return $this->driver->push(new ChildAssetsJob($params), $delay);
    }

    public function incomeInfo($params, int $delay = 0): bool
    {
        return $this->driver->push(new IncomeInfoJob($params), $delay);
    }

    public function userInfo($params, int $delay = 0): bool
    {
        return $this->driver->push(new UserInfoJob($params), $delay);
    }

    public function pullOut(string $coin_symbol, int $sort, int $delay = 0)
    {
        if ($this->redis()->setnx('pull_out_%s_%d', $coin_symbol, $sort)) {
            return $this->driver->push(new PullOutJob(['coin_symbol' => $coin_symbol, 'sort' => $sort]), $delay);
        } else {
            throw new \Exception('正在撤仓中，请稍后再试！');
        }
    }
}
