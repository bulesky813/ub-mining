<?php

namespace App\Services;

use App\Services\Base\BaseModelService;
use App\Services\Base\BaseRedisService;

class AbstractService
{
    use BaseModelService, BaseRedisService;

    protected $modelClass = '';
}
