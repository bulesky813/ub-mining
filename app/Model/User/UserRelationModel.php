<?php

declare (strict_types=1);

namespace App\Model\User;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class UserRelationModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_relation';
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
        'parent_user_ids' => 'array',
        'child_user_ids' => 'array'
    ];

    public function user()
    {
        return $this->hasOne('App\Model\User\UsersModel', 'id', 'user_id');
    }

    public function assets()
    {
        return $this->hasMany('App\Model\User\UserAssetsModel', 'user_id', 'user_id');
    }
}
