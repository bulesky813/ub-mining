<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicSmallIncomeConfigService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicSmallIncomeConfigModel';

    public function configCreate($params)
    {
        try {
            $data = $this->get([
                'coin_symbol' => $params['coin_symbol'],
            ]);
            if ($data) {
                throw new \Exception('数据存在');
            }
            $data = $this->create([
                'coin_symbol' => $params['coin_symbol'],
                'percent' => $params['percent'],
            ]);
            return $data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function configUpdate($params)
    {
        try {
            $data = $this->get([
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

    public function getConfig($params)
    {
        try {
            $data = $this->findByAttr([
                'coin_symbol' => $params['coin_symbol'],
            ]);
            if (!$data) {
                throw new \Exception('数据不存在');
            }
            return $data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
