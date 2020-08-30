<?php

namespace App\Services\User;

use App\Services\AbstractService;
use App\Services\User\UsersService;
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
            $params['num'] = [
                'condition' => 'function',
                'data' => function ($query) use ($symbol) {
                    $query->where('num', $symbol, 0);
                }
            ];
            unset($params['action']);
        }
        $params['with'] = ['user'];
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
