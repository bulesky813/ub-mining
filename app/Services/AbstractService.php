<?php

namespace App\Services;

use App\Services\Base\BaseModelService;

class AbstractService
{
    use BaseModelService;

    protected $modelClass = '';
}
