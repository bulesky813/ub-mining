<?php

namespace App\Services\Income;

use App\Services\AbstractService;
use App\Services\Base\BaseRewardService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;

class StaticIncomeService extends AbstractService
{
    use BaseRewardService;
    protected $modelClass = 'App\Model\Income\StaticIncomeModel';

    public function createIncome(array $attr)
    {
        return $this->create($attr);
    }

    public function updateIncome(array $condition, array $attr)
    {
        return $this->update($condition, $attr);
    }

    public function findStaticIncome(array $attr)
    {
        return $this->get($attr);
    }

    public function listStaticIncome(array $attr)
    {
        return $this->findByAttr($attr);
    }

    public function sumIncome(array $sum_column_names, array $attr)
    {
        return $this->sum($sum_column_names, $attr);
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
                    $query->where('created_at', '<=', $date . ' 23:59:59');
                    $query->where('created_at', '>=', $date . ' 00:00:00');
                }
            ];
        }
        if (isset($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        return $this->findByAttr($where);
    }
}
