<?php

namespace App\Services\User;

use App\Model\User\UsersModel;
use App\Model\User\UserWarehouseRecordModel;
use App\Services\AbstractService;
use App\Services\Mine\MinePoolService;
use Carbon\Carbon;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\Inject;

class UserWarehouseRecordService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserWarehouseRecordModel';

    /**
     * @Inject
     * @var MinePoolService
     */
    protected $mps;

    public function record(array $attr): Model
    {
        $user_warehouse_record = $this->create($attr);
        return $user_warehouse_record;
    }

    public function todayRevoke(int $user_id, string $coin_symbol)
    {
        $min_pool_config = $this->mps->mineBaseConfigGet([
            'coin_symbol' => $coin_symbol,
        ]);
        $time_interval = $min_pool_config ? $min_pool_config->config->enable_time ?? 24 : 24;
        return $this->get([
            'user_id' => $user_id,
            'num' => function ($query) {
                $query->where('num', '<', 0);
            },
            'coin_symbol' => $coin_symbol,
            'created_at' => function ($query) use ($time_interval) {
                $query->whereBetween('created_at', [
                    Carbon::now()->subMinutes($time_interval * 60)->toDateTimeString(),
                    Carbon::now()->toDateTimeString()
                ]);
            }
        ]);
    }

    /**
     * 用户分仓记录搜索
     * @param $params
     * @return \Hyperf\Utils\Collection
     */
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
            $params['created_at'] = [
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
