<?php

namespace App\Services\Income;

use App\Services\AbstractService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;

class IncomeStatisticsService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\IncomeStatisticsModel';
}
