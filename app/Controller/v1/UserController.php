<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\User\UserAiWarehouseRequest;
use App\Request\User\UserChangeAssetsRequest;
use App\Request\User\UserCoinSymbolInfoRequest;
use App\Request\User\UserRelationRequest;
use App\Request\User\UserStaticIncomeRequest;
use App\Request\User\UserWarehouseRequest;
use App\Request\User\UserWarehouseRecordRequest;
use App\Services\Income\StaticIncomeService;
use App\Services\Queue\QueueService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class UserController extends AbstractController
{
    /**
     * @Inject
     * @var QueueService
     */
    protected $qs;

    /**
     * @Inject
     * @var UserWarehouseService
     */
    protected $uws;

    /**
     * @Inject
     * @var StaticIncomeService
     */
    protected $sis;

    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

    /**
     * @Inject
     * @var UserWarehouseRecordService
     */
    protected $uwrs;

    /**
     * @Inject
     * @var SeparateWarehouseService
     */
    protected $sws;

    public function relation(UserRelationRequest $request)
    {
        $user_id = (int)$request->input('user_id', 0);
        $parent_id = (int)$request->input('parent_id', 0);
        Db::beginTransaction();
        try {
            $user = $this->urs->bind($user_id, $parent_id);
            Db::commit();
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function changeAssets(UserChangeAssetsRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $separate_warehouse_sort = (int)$request->input('separate_warehouse_sort');
        $value = (string)$request->input('value');
        Db::beginTransaction();
        try {
            $user_warehouse = $this->uws->setUserWarehouse($user_id, $coin_symbol, $separate_warehouse_sort, $value);
            $user_warehouse_record = $this->uwrs->record([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'sort' => $separate_warehouse_sort,
                'value_before' => bcsub((string)$user_warehouse->assets, $value),
                'num' => $value
            ]);
            Db::commit();
            $this->qs->childAssets([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'value' => $value
            ]);
            return $this->success($user_warehouse_record->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function warehouse(UserWarehouseRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        try {
            $user_warehouses = $this->uws->userWarehouse($user_id, $coin_symbol);
            return $this->success($user_warehouses->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function staticIncome(UserStaticIncomeRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol', '') ?: '';
        try {
            $user_static_incomes = $this->sis->listStaticIncome([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'order' => 'created_at desc'
            ]);
            return $this->success($user_static_incomes->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 用户分仓记录列表查询
     * @param UserWarehouseRecordRequest $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserWarehouseRecord(UserWarehouseRecordRequest $request)
    {
        try {
            $params = $request->all();
            $data = $this->uwrs->getList($params);
            $data = $data->toArray();
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function userCoinSymbolInfo(UserCoinSymbolInfoRequest $request)
    {
        try {
            $user_id = $request->input('user_id');
            $coin_symbol = $request->input('coin_symbol');
            $userWarehouseAssets = $this->uws->sumAssets([
                'total_assets' => 'assets',
                'yesterday_income' => "income_info->'$.yesterday_income'",
                'total_income' => "income_info->'$.total_income'"
            ], [
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'assets' => function ($query) {
                    $query->where('assets', '>', 0);
                }
            ]);
            $userWarehouseAssets = $userWarehouseAssets->toArray();
            foreach ($userWarehouseAssets as $key => $value) {
                $userWarehouseAssets[$key] = $value == 'null' || is_null($value) ? 0 : $value;
            }
            return $this->success($userWarehouseAssets);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function userAiWarehouse(UserAiWarehouseRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $assets = (string)$request->input('assets');
        try {
            $separate_warehouse = $this->sws->separateWarehouse($coin_symbol);
            $user_warehouse = $this->uws->userWarehouse($user_id, $coin_symbol);
            $outputs = [];
            foreach ($separate_warehouse as $currency_separate_warehouse) {
                $user_currency_warehouse = $user_warehouse
                    ->firstWhere('sort', $currency_separate_warehouse->sort);
                if (!$user_currency_warehouse || $user_currency_warehouse->assets < $currency_separate_warehouse->high) {
                    $ai_assets = bcsub(
                        (string)$currency_separate_warehouse->high,
                        (string)$user_currency_warehouse->assets ?? '0'
                    );
                    $outputs[] = [
                        'coin_symbol' => $coin_symbol,
                        'sort' => $currency_separate_warehouse->sort,
                        'assets' => bccomp($assets, $ai_assets) < 0 ? $assets : $ai_assets
                    ];
                    $assets = bcsub($assets, $ai_assets);
                    if (bccomp($assets, '0') <= 0) {
                        break;
                    }
                }
            }
            return $this->success($outputs);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
