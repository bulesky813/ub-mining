<?php

declare(strict_types=1);

namespace App\Services\Queue;

use App\Job\ChildAssetsJob;
use App\Job\IncomeInfoJob;
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
}
