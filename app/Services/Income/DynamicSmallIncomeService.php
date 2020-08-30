<?php

namespace App\Services\Income;

use App\Services\AbstractService;
use App\Services\User\UsersService;

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
            $params['paginate'] = true;
        }
        if (isset($params['coin_symbol'])) {
            $params['coin_symbol'] = $params['coin_symbol'];
        }
        if (isset($params['date'])) {
            $params['create_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date']));
                    $query->where('created_at', '<=', $date.' 23:59:59');
                    $query->where('created_at', '>=', $date.' 00:00:00');
                }
            ];
            unset($params['date']);
        }
        if (isset($params['user_id'])) {
            $params['user_id'] = $params['user_id'];
        }

        return $this->findByAttr($params);
    }

    public function formatShowData($list)
    {
        $search_uid = [];
        foreach ($list as $k => $v) {
            $search_uid[] = $v['user_id'];
        }
        $userSer = new UsersService();
        $userList = $userSer->findByAttr(['id' => $search_uid]);
        return $userList;
    }
}
