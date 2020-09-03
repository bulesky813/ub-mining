<?php

namespace App\Services\Mine;

use App\Model\Mine\MineCoinModel;
use App\Services\AbstractService;
use App\Services\Income\DynamicSmallIncomeConfigService;
use App\Services\Income\ExcludeRewardsUsersService;
use App\Services\Separate\SeparateWarehouseService;

class MinePoolService extends AbstractService
{
    protected $modelClass = 'App\Model\Mine\MinePoolModel';

    /**
     * 矿池是否开启
     * @param $coin_symbol
     * @return bool
     * @throws \Throwable
     */
    public function isOpenMine($coin_symbol)
    {
        try {
            $mine = $this->get(['coin_symbol' => $coin_symbol, 'status' => 1]);
            if ($mine) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 创建矿池
     * @param $params
     * @return array
     * @throws \Throwable
     */
    public function createMine($params)
    {
        try {
            $coin = $this->getCoin($params['coin_symbol']);
            $params['coin_id'] = $coin['id'];

            $exist = $this->get(['coin_symbol' => $params['coin_symbol']]);
            if ($exist) {
                throw new \Exception('该币种已存在矿池');
            }
            $mine = $this->create([
                'coin_id' => $params['coin_id'],
                'coin_symbol' => $params['coin_symbol'],
                'min_amount' => isset($params['min_amount']) ? $params['min_amount'] : 0,
                'max_amount' => isset($params['max_amount']) ? $params['max_amount'] : 0,
            ]);
            return $mine->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 更新矿池信息
     * @param $params
     * @return mixed
     * @throws \Throwable
     */
    public function updateMine($params)
    {
        try {
            $coin = $this->getCoin($params['coin_symbol']);
            $params['coin_id'] = $coin['id'];

            $exist = $this->get(['coin_symbol' => $params['coin_symbol']]);
            if ($exist && $exist->id != $params['pool_id']) {
                throw new \Exception('该币种已存在矿池');
            }
            $mine = $this->get(['id' => $params['pool_id']]);
            if ($mine) {
                if ($params['status'] == 1) {
                    if (empty($mine->config)) {
                        throw new \Exception('矿池配置为空');
                    }
                    $this->checkPoolConfig($params['coin_symbol']);
                }
                $mine->status = $params['status'];
                $mine->coin_id = $params['coin_id'];
                $mine->coin_symbol = $params['coin_symbol'];
                if (isset($params['min_amount'])) {
                    $mine->min_amount = $params['min_amount'];
                }
                if (isset($params['max_amount'])) {
                    $mine->max_amount = $params['max_amount'];
                }
                if (!$mine->save()) {
                    throw new \Exception('更新失败');
                }
            } else {
                throw new \Exception('矿池不存在');
            }

            return $mine->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkPoolConfig($coin_symbol)
    {
        try {
            $sws = (new SeparateWarehouseService)->separateWarehouse($coin_symbol);
            if ($sws->isEmpty()) {
                throw new \Exception('分仓没配置');
            }
            $small_config = (new DynamicSmallIncomeConfigService())->getConfig(['coin_symbol' => $coin_symbol]);
            if ($small_config->isEmpty()) {
                throw new \Exception('动态小区没配置');
            }
            $ex_user = (new ExcludeRewardsUsersService())->findByAttr(['coin_symbol' => $coin_symbol]);
            if ($ex_user->isEmpty()) {
                throw new \Exception('排除奖励ID没配置');
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function mineList($params)
    {
        try {
            $list = $this->findByAttr($params);
            return $list;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 获取币信息
     * @param $coin_symbol
     * @return mixed
     * @throws \Throwable
     */
    public function getCoin($coin_symbol)
    {
        try {
            $coin = MineCoinModel::where([
                'coin_symbol' => $coin_symbol
            ])->first();
            if (!$coin) {
                throw new \Exception('币种不存在');
            }
            return $coin->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 创建币种
     * @param $params
     * @return array
     * @throws \Throwable
     */
    public function coinCreate($params)
    {
        try {
            $coin = MineCoinModel::where([
                'coin_symbol' => $params['coin_symbol']
            ])->first();
            if ($coin) {
                throw new \Exception('币种已存在');
            }
            $coin = new MineCoinModel;
            $coin->coin_symbol = $params['coin_symbol'];
            $coin->coin_icon = isset($params['icon']) ? $params['icon'] : '';
            $coin->coin_price = isset($params['coin_price']) ? $params['coin_price'] : '';
            if (!$coin->save()) {
                throw new \Exception('币种保存失败');
            }
            return $coin->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 更新币种
     * @param $params
     * @return mixed
     * @throws \Throwable
     */
    public function coinUpdate($params)
    {
        try {
            $coin = MineCoinModel::where([
                'coin_symbol' => $params['coin_symbol']
            ])->first();
            if (!$coin) {
                throw new \Exception('币种不存在');
            }
            $coin->coin_icon = isset($params['icon']) ? $params['icon'] : '';
            $coin->coin_price = isset($params['coin_price']) ? $params['coin_price'] : '';
            if (!$coin->save()) {
                throw new \Exception('币种更新失败');
            }
            return $coin->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function mineBaseConfigSave($params)
    {
        try {
            $data = $this->get(['coin_symbol' => $params['coin_symbol']]);
            //每N小时撤仓一次
            if (isset($params['enable_time']) && !empty($params['enable_time'])) {
                $config['enable_time'] = $params['enable_time'];
            } else {
                $config['enable_time'] = 24;
            }
            //分仓条件 1最小持仓 2满仓
            if (isset($params['raise_condition'])
                && in_array($params['raise_condition'], [1, 2])) {
                $config['raise_condition'] = $params['raise_condition'];
            } else {
                $config['raise_condition'] = 2;
            }
//            $data->config = json_encode($config, JSON_UNESCAPED_UNICODE);
            $data->config = $config;
            if (!$data->save()) {
                throw new \Exception('保存失败');
            }
            return $data;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function mineBaseConfigGet($params)
    {
        try {
            $data = $this->get(['coin_symbol' => $params['coin_symbol']]);
            return $data;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function raiseCondition(string $coin_symbol): int
    {
        $mine_pool_config = $this->mps->mineBaseConfigSave([
            'coin_symbol' => $coin_symbol
        ]);
        return $mine_pool_config ? $mine_pool_config->config->raise_condition ?? 2 : 2;
    }
}
