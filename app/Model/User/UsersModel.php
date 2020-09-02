<?php

declare (strict_types=1);

namespace App\Model\User;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class UsersModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
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
    protected $casts = [];

    public $incrementing = false;

    public function userRelation()
    {
        return $this->hasOne('App\Model\User\UserRelationModel', 'user_id', 'id');
    }

    public function userAssets()
    {
        return $this->hasMany('App\Model\User\UserAssetsModel', 'user_id', 'id');
    }
}
