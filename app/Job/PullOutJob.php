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
        $timestamp = (string)time();
        $token = hash_hmac('sha256', $timestamp, config('mining.app_secret_key'));
        $coin_symbol = (string)Arr::get($this->params, 'coin_symbol', 0);
        $sort = (int)Arr::get($this->params, 'sort', 0);
        $this->uws->userWarehouseList($coin_symbol, $sort, [
            'chunk' => [$this, 'chunk']
        ]);
    }

    public function chunk(Collection $user_warehouse_list)
    {
        $user_warehouse_list->each(function ($user_warehouse, $key) {
            var_dump($user_warehouse->user_id, $user_warehouse->assets);
        });
    }
}
