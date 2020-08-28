<?php

declare (strict_types=1);

namespace App\Model\Income;

use App\Model\AbstractModel;
use Hyperf\DbConnection\Model\Model;

/**
 */
class IncomeStatisticsModel extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'income_statistics';
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
}
