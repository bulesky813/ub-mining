<?php

namespace App\Services\Income;

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
        return $this->findByAttr($where);
    }
}
