<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UserWarehouseRecordService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserWarehouseRecordModel';

    public function record(array $attr): Model
    {
        $user_warehouse_record = $this->create($attr);
        return $user_warehouse_record;
    }
}
