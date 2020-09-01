<?php

namespace App\Services\Income;

use App\Model\User\UsersModel;
use App\Services\AbstractService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Arr;

class IncomeStatisticsService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\IncomeStatisticsModel';

    public function createStatistics(array $attr)
    {
        $day = Arr::get($attr, 'day', '');
        $coin_symbol = Arr::get($attr, 'coin_symbol', '');
        $statistics = $this->get(['day' => $day, 'coin_symbol' => $coin_symbol]);
        if (!$statistics) {
            return $this->create($attr);
        } else {
            unset($attr['day'], $attr['coin_symbol']);
            $this->update(['id' => $statistics->id], $attr);
            return $statistics;
        }
    }

    public function findStatistics(array $attr)
    {
        return $this->get($attr);
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
        if (isset($params['date_start']) && !isset($params['date_end'])) {
            $params['created_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date_start']));
                    $query->where('created_at', '>=', $date.' 00:00:00');
                }
            ];
            unset($params['date_start']);
        }
        if (isset($params['date_end']) && !isset($params['date_start'])) {
            $params['created_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date = date('Y-m-d', strtotime($params['date_end']));
                    $query->where('created_at', '<=', $date.' 23:59:59');
                }
            ];
            unset($params['date_end']);
        }
        if (isset($params['date_start']) && isset($params['date_end'])) {
            $params['created_at'] = [
                'condition' => 'function',
                'data' => function ($query) use ($params) {
                    $date_start = date('Y-m-d', strtotime($params['date_start']));
                    $date_end = date('Y-m-d', strtotime($params['date_end']));
                    $query->whereBetween(
                        'created_at',
                        [$date_start.' 00:00:00', $date_end.' 23:59:59']
                    );
                }
            ];
            unset($params['date_start']);
            unset($params['date_end']);
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
                    $query->where('id', '>', $page_max_id);
                }
            ];
        }
        if (isset($params['page_min_id']) && $params['page_min_id'] > 0) {
            $page_min_id = $params['page_min_id'];
            unset($params['page_min_id']);
            unset($params['pn']);
            $params['id'] = [
                'condition' => 'function',
                'data' => function ($query) use ($page_min_id) {
                    $query->where('id', '<', $page_min_id);
                }
            ];
        }
        $params['with'] = ['user'];
        return $this->findByAttr($params);
    }
}
