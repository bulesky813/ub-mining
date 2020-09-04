<?php

namespace App\Services\Income;

use App\Model\User\UsersModel;
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
        if (isset($params['address'])) {
            $user_address2id = UsersModel::where([
                'origin_address' => $params['address']
            ])->orWhere(['id' => $params['address']])->first();
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
        if (isset($params['page_max_id']) && $params['page_max_id'] > 0) {
            $page_max_id = $params['page_max_id'];
            unset($params['page_max_id']);
            unset($params['pn']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($page_max_id) {
//                    $query->where('id', '>', $page_max_id);
                    $query->where('id', '<', $page_max_id);
                }
            ];
        }
        if (isset($params['page_min_id']) && $params['page_min_id'] > 0) {
            $page_min_id = $params['page_min_id'];
            $find_ids = $this->getPrePageIds($params);
            unset($params['page_min_id']);
            unset($params['pn']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($find_ids) {
                    $query->whereIn('id', $find_ids);
                }
            ];
        }
        $params['with'] = ['user'];
        $params['order'] = 'id desc';
        return $this->findByAttr($params);
    }

    public function getPrePageIds($params)
    {
        $where = [];
        $where['ps'] = isset($params['ps']) ? $params['ps'] : 10 ;
        if (isset($params['coin_symbol'])) {
            $where['coin_symbol'] = $params['coin_symbol'];
        }
        if (isset($params['address'])) {
            $user_address2id = UsersModel::where([
                'origin_address' => $params['address']
            ])->orWhere(['id' => $params['address']])->first();
            if ($user_address2id) {
                $user = $user_address2id->toArray();
                $where['user_id'] = $user['id'];
            } else {
                $where['user_id'] = 0;
            }
            unset($params['address']);
        }
        if (isset($params['date'])) {
            $where['created_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date']));
                    $query->where('created_at', '<=', $date . ' 23:59:59');
                    $query->where('created_at', '>=', $date . ' 00:00:00');
                }
            ];
            unset($params['date']);
        }
        if (isset($params['page_min_id'])) {
            $page_min_id = $params['page_min_id'];
            $where['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($page_min_id) {
                    $query->where('id', '>', $page_min_id);
                }
            ];
            $where['paginate'] = true;
        }
        $pre_data = $this->findByAttr($where);
        $find_ids = [];
        $find_ids = array_column($pre_data->toArray(), 'id');
        return $find_ids;
    }
}
