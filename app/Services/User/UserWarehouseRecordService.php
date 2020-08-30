<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Carbon\Carbon;
use Hyperf\Database\Model\Model;
use function Zipkin\Timestamp\now;

class UserWarehouseRecordService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserWarehouseRecordModel';

    public function record(array $attr): Model
    {
        $user_warehouse_record = $this->create($attr);
        return $user_warehouse_record;
    }

    public function todayRevoke(int $user_id)
    {
        return $this->get([
            'user_id' => $user_id,
            'num' => [
                'condition' => 'function',
                'data' => function ($query) {
                    $query->where('num', '<', 0);
                }
            ],
            'created_at' => [
                'condition' => 'function',
                'data' => function ($query) {
                    $query->whereBetween(
                        'created_at',
                        [
                            Carbon::now()->startOfDay()->toDateTimeString(),
                            Carbon::now()->toDateTimeString()
                        ]
                    );
                }
            ]
        ]);
    }

    /**
     * 用户分仓记录搜索
     * @param $params
     * @return \Hyperf\Utils\Collection
     */
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
        if (isset($params['action']) && in_array($params['action'], ['up', 'down'])) {
            $symbol = '>';
            switch ($params['action']) {
                case 'up':
                    $symbol = '>';
                    break;
                case 'down':
                    $symbol = '<';
                    break;
            }
            $where['num'] = [
                'condition' => 'function',
                'data' => function ($query) use ($symbol) {
                    $query->where('num', $symbol, 0);
                }
            ];
        }
        return $this->findByAttr($where);
    }
}
