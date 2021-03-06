<?php

namespace App\Services\User;

use App\Model\User\UserWarehouseModel;
use App\Services\AbstractService;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;

class UserWarehouseService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserWarehouseModel';
    protected $uas = null;

    public function __construct()
    {
        $this->uas = new UserAssetsService();
    }

    /**
     * 用户单币种持仓列表
     *
     * @param int $user_id
     * @param string $coin_symbol
     * @param bool $has_history
     * @return \Hyperf\Utils\Collection
     */
    public function userWarehouse(int $user_id, string $coin_symbol, bool $has_history = false)
    {
        return $this->findByAttr([
            'user_id' => $user_id,
            'coin_symbol' => $coin_symbol,
            'assets' => $has_history == true ? 0 : [
                'condition' => 'function',
                'data' => function ($query) {
                    $query->where('assets', '>', 0);
                }
            ],
            'order' => 'sort asc',
        ]);
    }

    /**
     * 设置用户持仓值
     *
     * @param int $user_id
     * @param string $coin_symbol
     * @param int $sort
     * @param string $value
     * @return Model
     */
    public function setUserWarehouse(int $user_id, string $coin_symbol, int $sort, string $value): Model
    {
        $this->uas->userAssets($user_id, $coin_symbol, $value); //改变用户总持币量
        $user_warehouse = $this->get([
            'user_id' => $user_id,
            'coin_symbol' => $coin_symbol,
            'sort' => $sort
        ]);
        if ($user_warehouse) {
            $user_warehouse->increment('assets', $value);
        } else {
            $user_warehouse = $this->create([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'sort' => $sort,
                'assets' => $value,
                'income_info' => [
                    'total_income' => 0,
                    'yesterday_income' => 0
                ]
            ]);
        }
        return $user_warehouse;
    }

    public function maxWarehouseSort(int $user_id, string $coin_symbol): int
    {
        $user_warehouse = $this->max(['separate_warehouse_max_sort' => 'sort'], [
            'user_id' => $user_id,
            'coin_symbol' => $coin_symbol,
            'assets' => function ($query) {
                $query->where('assets', '>', 0);
            }
        ]);
        return $user_warehouse->separate_warehouse_max_sort ?? 0;
    }

    public function maxWarehouseSortByAssets(int $user_id, string $coin_symbol): int
    {
        $user_warehouse = $this->max(['separate_warehouse_max_sort' => 'sort'], [
            'user_id' => $user_id,
            'coin_symbol' => $coin_symbol,
            'assets' => [
                'condition' => 'function',
                'data' => function ($query) {
                    $query->where('assets', '>', 0);
                }
            ],
        ]);
        return $user_warehouse->separate_warehouse_max_sort ?? 0;
    }

    public function updateIncomeInfo(int $user_id, string $coin_symbol, int $sort, string $assets)
    {
        UserWarehouseModel::query()
            ->where('user_id', $user_id)
            ->where('coin_symbol', $coin_symbol)
            ->where('sort', $sort)
            ->update([
                'income_info' => Db::raw("json_set(income_info, '$.yesterday_income', {$assets}, '$.total_income', IFNULL(income_info->'$.total_income', 0) + {$assets})"),
            ]);
    }

    public function sumAssets(array $sum_column_names, array $attr)
    {
        return $this->sum($sum_column_names, $attr);
    }

    public function userWarehouseList(string $coin_symbol, int $sort, array $attr)
    {
        return $this->findByAttr([
                'coin_symbol' => $coin_symbol,
                'sort' => $sort
            ] + $attr);
    }
}
