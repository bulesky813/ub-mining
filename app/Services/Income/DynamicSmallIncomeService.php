<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicSmallIncomeService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicSmallIncomeModel';

    public function createIncome(array $attr)
    {
        return $this->create($attr);
    }

    public function findSmallIncome(array $attr)
    {
        return $this->get($attr);
    }
}
