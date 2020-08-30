<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicSmallIncomeService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicSmallIncomeModel';

    public function createIncome(array $attr)
    {
        return $this->create($attr);
    }

    public function findSmallIncome(array $attr)
    {
        return $this->get($attr);
    }

    public function getList($params)
    {
        $where = [];
        //分页
        if (isset($params['last_max_id']) && $params['last_max_id'] > 1) {
            $where['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $query->where('id', '>', $params['last_max_id']);
                }
            ];
            unset($params['last_max_id']);
            $where['paginate'] = true;
        }
        if (isset($params['coin_symbol'])) {
            $where['coin_symbol'] = $params['coin_symbol'];
        }
        if (isset($params['date'])) {
            $where['create_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date']));
                    $query->where('created_at', '<=', $date.' 23:59:59');
                    $query->where('created_at', '>=', $date.' 00:00:00');
                }
            ];
        }
        if (isset($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        return $this->findByAttr($where);
    }
}
