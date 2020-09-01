<?php

namespace App\Services\Separate;

use App\Services\AbstractService;
use App\Services\Mine\MinePoolService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;

class SeparateWarehouseService extends AbstractService
{
    protected $modelClass = 'App\Model\Separate\SeparateWarehouseModel';
    private $urs = null;

    public function __construct()
    {
        $this->urs = new UserWarehouseService();
    }

    public function separateWarehouse(string $coin_symbol, $sort = null)
    {
        return $this->findByAttr([
            'coin_symbol' => $coin_symbol,
            'sort' => $sort,
            'order' => 'sort asc'
        ]);
    }

    public function separateWarehouseList($params)
    {
        try {
            $where = [
                'coin_symbol' => $params['coin_symbol'],
                'order' => 'sort desc'
            ];
            $list = $this->findByAttr($where);
            return $list;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function separateWarehouseCreate($params)
    {
        try {
            //检查矿池是否开启
            $mine = new MinePoolService();
            if (!$mine->isOpenMine($params['coin_symbol'])) {
                throw new \Exception('矿池没开启');
            }

            //检查分仓是否存在
            $exist = $this->get([
                'coin_symbol' => $params['coin_symbol'],
                'sort' => $params['sort'],
            ]);
            if ($exist) {
                throw new \Exception('分仓已存在');
            }

            //检查分仓区间是否合理
            $this->checkSWLow($params);

            $sw = $this->create([
                'coin_symbol' => $params['coin_symbol'],
                'sort' => $params['sort'],
                'low' => $params['low'],
                'high' => $params['high'],
                'percent' => $params['percent'],
            ]);
            return $sw->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIsExist($params)
    {
        try {
            //检查分仓是否存在
            $exist = $this->get([
                'coin_symbol' => $params['coin_symbol'],
                'sort' => $params['sort'],
            ]);
            if (!$exist) {
                throw new \Exception('分仓不存在');
            }
            return $exist;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 检查最小持币量
     * @param $params
     * @throws \Throwable
     */
    public function checkSWLow($params)
    {
        try {
            //检查分仓区间是否合理
            $last_sort = $params['sort'] - 1;
            if ($last_sort > 1) {
                $last_data = $this->get([
                    'coin_symbol' => $params['coin_symbol'],
                    'sort' => $last_sort,
                ]);
                if ($params['low'] < $last_data->high) {
                    throw new \Exception('当前分仓最小持币量必须大于等于上一仓的最大持币量');
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 检查最大持币量
     * @param $params
     * @throws \Throwable
     */
    public function checkSWHigh($params)
    {
        try {
            //检查分仓区间是否合理
            $next_sort = $params['sort'] + 1;
            $next_data = $this->get([
                'coin_symbol' => $params['coin_symbol'],
                'sort' => $next_sort,
            ]);
            if ($params['high'] > $next_data->low) {
                throw new \Exception('当前分仓最大持币量必须小于等于下一仓的最小持币量');
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 分仓修改
     * @param $params
     * @return array
     * @throws \Throwable
     */
    public function separateWarehouseUpdate($params)
    {
        try {
            $sw_data = $this->checkIsExist($params);
            $this->checkSWLow($params);
            $this->checkSWHigh($params);

            $sw_data->percent = $params['percent'];
            if (!$sw_data->save()) {
                throw new \Exception('更新失败');
            }
            return $sw_data->toArray();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 分仓删除
     * @param $params
     * @return bool
     * @throws \Throwable
     */
    public function separateWarehouseDel($params)
    {
        try {
            $sw_data = $this->checkIsExist($params);

            $has_last = $this->get([
                'coin_symbol' => $params['coin_symbol'],
                'sort' => $params['sort'] + 1,
            ]);
            if ($has_last) {
                throw new \Exception('不是最后一个分仓');
            }

            if (!$sw_data->delete()) {
                throw new \Exception('删除失败');
            }

            //TODO 发送rb消息清除所有该分仓的持仓

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getUserMaxSortRate($coin_symbol, $sort)
    {
        try {
            $data = $this->get([
                'coin_symbol' => $coin_symbol,
                'sort' => $sort,
            ]);
            if ($data) {
                return $data->percent;
            } else {
                return 0;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
