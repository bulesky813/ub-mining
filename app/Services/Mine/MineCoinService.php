<?php

namespace App\Services\Mine;

use App\Model\Mine\MineCoinModel;
use App\Services\AbstractService;

class MineCoinService extends AbstractService
{
    protected $modelClass = 'App\Model\Mine\MineCoinModel';

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

    public function coinList($params)
    {
        try {
            $list = $this->findByAttr([]);
            return $list;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function coinSync($params)
    {
        try {
            foreach ($params['data'] as $k => $v) {
                $coin = $this->get(['coin_symbol' => $v['symbol']]);
                if ($coin) {
                    $coin->coin_icon = $v['icon'];
                    $coin->save();
                } else {
                    $this->create([
                        'id' => $v['id'],
                        'coin_symbol' => $v['symbol'],
                        'coin_icon' => $v['icon'],
                        'coin_price' => 0,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
