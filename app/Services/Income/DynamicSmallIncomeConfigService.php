<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicSmallIncomeConfigService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicSmallIncomeConfigModel';

    public function create($params)
    {
        try {
            $data = $this->create([
                'coin_symbol' => $params['coin_symbol'],
                'percent' => $params['percent'],
            ]);
            return $data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update($params)
    {
        try {
            $data = $this->get([
                'config_id' => $params['config_id'],
                'coin_symbol' => $params['coin_symbol'],
            ]);
            if (!$data) {
                throw new \Exception('数据不存在');
            }

            $data->percent = $params['percent'];
            if (!$data->save()) {
                throw new \Exception('更新失败');
            }
            return $data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function del($params)
    {
        try {
            $data = $this->get([
                'config_id' => $params['config_id'],
                'coin_symbol' => $params['coin_symbol'],
            ]);
            if (!$data) {
                throw new \Exception('数据不存在');
            }
            if (!$data->delete()) {
                throw new \Exception('删除失败');
            }
            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
