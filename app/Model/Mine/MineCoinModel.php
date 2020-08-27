<?php

declare (strict_types=1);
namespace App\Model\Mine;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class MineCoinModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mine_coin';
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
