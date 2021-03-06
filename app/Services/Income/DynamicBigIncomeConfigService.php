<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicBigIncomeConfigService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicBigIncomeConfigModel';

    public function configCreate($params)
    {
        try {
            //获取排序值
            $exist = $this->get([
                'coin_symbol' => $params['coin_symbol'],
                'order' => 'sort DESC'
            ]);
            if ($exist) {
                $exist_data = $exist->toArray();
                $sort = $exist_data['sort'] + 1;
            } else {
                $sort = 1;
            }

            //判断num可以赋的值
            if ($sort > 1) {
                $pre = $this->get([
                    'coin_symbol' => $params['coin_symbol'],
                    'sort' => $sort - 1,
                ]);
                $pre_data = $pre->toArray();
                if ($pre_data['num'] <= $params['num']) {
                    throw new \Exception('设置数值必须大于前一个区');
                }
            }

            $data = $this->create([
                'sort' => $sort,
                'coin_symbol' => $params['coin_symbol'],
                'num' => $params['num'],
                'person_num' => $params['person_num'],
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
                'id' => $params['config_id'],
                'coin_symbol' => $params['coin_symbol'],
            ]);
            if (!$data) {
                throw new \Exception('数据不存在');
            }

            $data->num = $params['num'];
            $data->income = $params['person_num'];
            $data->percent = $params['percent'];
            if (!$data->save()) {
                throw new \Exception('更新失败');
            }
            return $data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function configDel($params)
    {
        try {
            $data = $this->get([
                'id' => $params['config_id'],
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

    public function getConfig($params)
    {
        try {
            $data = $this->findByAttr([
//                'id' => $params['config_id'],
                'coin_symbol' => $params['coin_symbol'],
            ]);
//            if (!$data) {
//                throw new \Exception('数据不存在');
//            }
            return $data;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
