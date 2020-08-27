<?php

namespace App\Services\Mine;

use App\Model\Mine\MineCoinModel;
use App\Services\AbstractService;

class MineService extends AbstractService
{
    protected $modelClass = 'App\Model\Mine\MinePoolModel';

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
                'min_amount' => $params['min_amount'],
                'max_amount' => $params['max_amount'],
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
                $mine->status = $params['status'];
                $mine->coin_id = $params['coin_id'];
                $mine->coin_symbol = $params['coin_symbol'];
                $mine->min_amount = $params['min_amount'];
                $mine->max_amount = $params['max_amount'];
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

    public function mineList($params)
    {
        try {
            $params['status'] = 1;
            $list = $this->findByAttr(['status' => $params['status']]);
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
            $coin->coin_icon = isset($params['icon'])?$params['icon']:'';
            $coin->coin_price = isset($params['coin_price'])?$params['coin_price']:'';
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
            $coin->coin_icon = isset($params['icon'])?$params['icon']:'';
            $coin->coin_price = isset($params['coin_price'])?$params['coin_price']:'';
            if (!$coin->save()) {
                throw new \Exception('币种更新失败');
            }
            return $coin->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
