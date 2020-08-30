<?php

declare (strict_types=1);

namespace App\Model\User;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class UserWarehouseRecordModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_warehouse_record';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    public function user()
    {
        return $this->hasOne('App\Model\User\UsersModel', 'id', 'user_id');
    }
}
