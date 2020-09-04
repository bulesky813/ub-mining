<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\Http\HttpService;
use App\Services\Mine\SeparateWarehouseService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UsersService;
use App\Services\User\UserWarehouseService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Database\Model\Collection;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;

class PullOutJob extends Job
{
    protected $params;

    /**
     * @Inject
     * @var HttpService
     */
    protected $hs;

    /**
     * @Inject
     * @var UsersService
     */
    protected $us;

    /**
     * @Inject
     * @var SeparateWarehouseService
     */
    protected $sws;

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
        $coin_symbol = (string)Arr::get($this->params, 'coin_symbol', 0);
        $sort = (int)Arr::get($this->params, 'sort', 0);
        $this->uws->userWarehouseList($coin_symbol, $sort, [
            'chunk' => [$this, 'chunk']
        ]);
    }

    public function chunk(Collection $user_warehouse_list)
    {
        $parallel = new Parallel(5);
        foreach ($user_warehouse_list as $user_warehouse) {
            $parallel->add(function () use ($user_warehouse) {
                $timestamp = (string)time();
                $token = hash_hmac('sha256', $timestamp, config('mining.app_secret_key'));
                $this->hs->lessFreeze([
                    'uid' => $user_warehouse,
                    'value' => $user_warehouse->assets,
                    'coin_symbol' => $user_warehouse->coin_symbol,
                    'time' => $timestamp,
                    'token'
                ]);
            });
        };
    }
}
