<?php

namespace App\Services\Income;

use App\Services\AbstractService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;

class StaticIncomeService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\StaticIncomeModel';

    public function createIncome(array $attr)
    {
        return $this->create($attr);
    }

    public function findStaticIncome(array $attr)
    {
        return $this->get($attr);
    }

    public function listStaticIncome(array $attr)
    {
        return $this->findByAttr($attr);
    }
}
