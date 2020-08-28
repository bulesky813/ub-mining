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
}
