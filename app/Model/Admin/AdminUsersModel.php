<?php

declare (strict_types=1);
namespace App\Model\Admin;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class AdminUsersModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_users';
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
}
