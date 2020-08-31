<?php

namespace App\Services\Income;

use App\Model\User\UsersModel;
use App\Services\AbstractService;
use App\Services\Base\BaseRewardService;

class DynamicSmallIncomeService extends AbstractService
{
    use BaseRewardService;

    protected $modelClass = 'App\Model\Income\DynamicSmallIncomeModel';

    public function createIncome(array $attr)
    {
        return $this->create($attr);
    }

    public function findSmallIncome(array $attr)
    {
        return $this->get($attr);
    }

    public function updateIncome(array $condition, array $attr)
    {
        return $this->update($condition, $attr);
    }

    public function sumIncome(array $sum_column_names, array $attr)
    {
        return $this->sum($sum_column_names, $attr);
    }

    public function getList($params)
    {
        if (isset($params['address'])) {
            $user_address2id = UsersModel::where([
                'origin_address' => $params['address']
            ])->first();
            if ($user_address2id) {
                $user = $user_address2id->toArray();
                $params['user_id'] = $user['id'];
            } else {
                $params['user_id'] = 0;
            }
            unset($params['address']);
        }
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
            unset($params['date']);
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
        if (isset($params['next_id']) && $params['next_id'] > 0) {
            $next_id = $params['next_id'];
            unset($params['next_id']);
            unset($params['pn']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($next_id) {
                    $query->where('id', '>', $next_id);
                }
            ];
        }
        if (isset($params['last_id']) && $params['last_id'] > 0) {
            $last_id = $params['last_id'];
            unset($params['last_id']);
            unset($params['pn']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($last_id) {
                    $query->where('id', '<', $last_id);
                }
            ];
        }
        $params['with'] = ['user'];
        return $this->findByAttr($params);
    }
}
