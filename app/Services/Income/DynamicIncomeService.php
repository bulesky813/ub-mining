<?php

namespace App\Services\Income;

use App\Services\AbstractService;

class DynamicIncomeService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\DynamicIncomeModel';

    public function createIncome($attr)
    {
        return $this->create($attr);
    }
}
