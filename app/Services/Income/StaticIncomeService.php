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
        if (isset($params['coin_symbol'])) {
            $params['coin_symbol'] = $params['coin_symbol'];
        }
        if (isset($params['date'])) {
            $params['create_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date']));
                    $query->where('created_at', '<=', $date . ' 23:59:59');
                    $query->where('created_at', '>=', $date . ' 00:00:00');
                }
            ];
        }
        if (isset($params['user_id'])) {
            $params['user_id'] = $params['user_id'];
        }

        //查询总数量
        if (isset($params['total_count'])) {
            unset($params['total_count']);
            return $this->count(['count' => 'id'], $params);
        }

        $params['paginate'] = true;
        //分页
        if (isset($params['last_max_id']) && $params['last_max_id'] > 0) {
            $last_max_id = $params['last_max_id'];
            unset($params['last_max_id']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($last_max_id) {
                    $query->where('id', '>', $last_max_id);
                }
            ];
        }
        $params['with'] = ['user'];

        return $this->findByAttr($params);
    }
}
