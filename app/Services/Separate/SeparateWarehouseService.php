<?php

namespace App\Services\Separate;

use App\Services\AbstractService;
use App\Services\User\UserWarehouseService;
use Hyperf\Database\Model\Model;

class SeparateWarehouseService extends AbstractService
{
    protected $modelClass = 'App\Model\Separate\SeparateWarehouseModel';
    private $urs = null;

    public function __construct()
    {
        $this->urs = new UserWarehouseService();
    }

    public function separateWarehouse(string $coin_symbol, $sort = null)
    {
        return $this->findByAttr([
            'coin_symbol' => $coin_symbol,
            'sort' => $sort,
            'order' => 'sort asc'
        ]);
    }
}
