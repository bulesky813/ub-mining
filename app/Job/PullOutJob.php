<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\Http\HttpService;
use App\Services\Mine\SeparateWarehouseService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UsersService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
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

    /**
     * @Inject
     * @var UserWarehouseRecordService
     */
    protected $uwrs;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $coin_symbol = (string)Arr::get($this->params, 'coin_symbol', 0);
        $sort = (int)Arr::get($this->params, 'sort', 0);
        $this->uws->userWarehouseList($coin_symbol, $sort, [
            'assets' => function ($query) {
                $query->where('assets', '>', 0);
            },
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
                $data = $this->hs->lessFreeze([
                    'uid' => $user_warehouse->user_id,
                    'value' => $user_warehouse->assets,
                    'coin_symbol' => $user_warehouse->coin_symbol,
                    'time' => $timestamp,
                    'token' => $token
                ]);
                if (!$data) {
                    return;
                }
                Db::beginTransaction();
                try {
                    $pullout_value = Arr::get($data, 'data.value', 0);
                    if (bccomp($pullout_value, $user_warehouse->assets) == 0) {
                        $user_warehouse->assets = 0;
                        $user_warehouse->save();
                    } else {
                        echo sprintf(
                            "user_id: %d, coin_symbol: %s, sort: %d, pullout: fail",
                            $user_warehouse->user_id,
                            $user_warehouse->coin_symbol,
                            $user_warehouse->sort
                        ) . PHP_EOL;
                        return;
                    }
                    $this->uwrs->record([
                        'user_id' => $user_warehouse->user_id,
                        'coin_symbol' => $user_warehouse->coin_symbol,
                        'sort' => $user_warehouse->sort,
                        'value_before' => $user_warehouse->assets,
                        'num' => bcmul($user_warehouse->assets, '-1'),
                        'pullout' => 2
                    ]);
                    Db::commit();
                } catch (\Throwable $e) {
                    Db::rollBack();
                    echo $e->getMessage() . PHP_EOL;
                }
            });
        };
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            throw $e;
        }
    }
}
