<?php

declare(strict_types=1);

namespace App\Services\Queue;

use App\Job\ChildAssetsJob;
use App\Job\IncomeInfoJob;
use App\Job\PullOutJob;
use App\Job\UserInfoJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
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
        return $this->driver->push(new PullOutJob(['coin_symbol' => $coin_symbol, 'sort' => $sort]), $delay);
    }
}
