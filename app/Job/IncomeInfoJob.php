<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UserWarehouseService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;

class IncomeInfoJob extends Job
{
    protected $params;

    /**
     * @Inject
     * @var UserWarehouseService
     */
    protected $uws;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $user_id = (int)Arr::get($this->params, 'user_id', 0);
        $coin_symbol = (string)Arr::get($this->params, 'coin_symbol', '');
        $percent = (string)Arr::get($this->params, 'percent', '0');
        if ($user_id == 0) {
            return;
        }
        $user_warehouses = $this->uws->userWarehouse($user_id, $coin_symbol);
        try {
            foreach ($user_warehouses as $user_warehouse) {
                $yesterday_income = bcmul($user_warehouse->assets, $percent);
                $this->uws->updateIncomeInfo(
                    $user_id,
                    $coin_symbol,
                    $user_warehouse->sort,
                    $yesterday_income
                );
            }
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}
