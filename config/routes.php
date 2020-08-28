<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::addGroup('/api/v1/user/', function () {
    Router::post('relation/set', 'App\Controller\v1\UserController@relation');
    Router::post('assets/set', 'App\Controller\v1\UserController@changeAssets');
    Router::post('warehouse/list', 'App\Controller\v1\UserController@warehouse');
    Router::post('static/income/list', 'App\Controller\v1\UserController@staticIncome');
});

Router::addGroup('/api/v1/mine/', function () {
    Router::post('index', 'App\Controller\v1\MineController@index');
    Router::post('create', 'App\Controller\v1\MineController@create');
    Router::post('update', 'App\Controller\v1\MineController@update');
    Router::post('coin_create', 'App\Controller\v1\MineController@coinCreate');
    Router::post('coin_update', 'App\Controller\v1\MineController@coinUpdate');
    Router::get('mine_list', 'App\Controller\v1\MineController@mineList');
    Router::get('coin_list', 'App\Controller\v1\MineController@coinList');
    Router::post('sw_create', 'App\Controller\v1\MineController@separateWarehouseCreate');
    Router::post('sw_update', 'App\Controller\v1\MineController@separateWarehouseUpdate');
    Router::get('sw_del', 'App\Controller\v1\MineController@separateWarehouseDel');
    Router::post(
        'dynamic/big_income_config_create',
        'App\Controller\v1\DynamicController@bigIncomeConfigCreate'
    );
    Router::post(
        'dynamic/big_income_config_update',
        'App\Controller\v1\DynamicController@bigIncomeConfigUpdate'
    );
    Router::get(
        'dynamic/big_income_config_del',
        'App\Controller\v1\DynamicController@bigIncomeConfigDel'
    );
    Router::get(
        'dynamic/big_income_config_get',
        'App\Controller\v1\DynamicController@bigIncomeConfigGet'
    );
});
