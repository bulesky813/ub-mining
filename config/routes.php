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

use App\Middleware\AdminMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::addGroup('/api/v1/user/', function () {
    Router::post('relation/set', 'App\Controller\v1\UserController@relation');
    Router::post('assets/set', 'App\Controller\v1\UserController@changeAssets');
    Router::post('warehouse/list', 'App\Controller\v1\UserController@warehouse');
    Router::post('static/income/list', 'App\Controller\v1\UserController@staticIncome');
    Router::post('coin/symbol/info/get', 'App\Controller\v1\UserController@userCoinSymbolInfo');
    Router::post('ai/warehouse/get', 'App\Controller\v1\UserController@userAiWarehouse');
    Router::get(
        'invitation/reward',
        'App\Controller\v1\DynamicController@smallIncomeList'
    );
    Router::get(
        'static/list',
        'App\Controller\v1\StaticController@staticIncomeList'
    );
    Router::get('mine_list', 'App\Controller\v1\MineController@mineList');
    Router::get('sw_list', 'App\Controller\v1\MineController@separateWarehouseList');
    Router::post('coin_sync', 'App\Controller\v1\MineController@coinSync');
});

Router::addGroup('/api/v1/admin/', function () {
    Router::post('login', 'App\Controller\v1\AdminController@login');
    Router::post('login_out', 'App\Controller\v1\AdminController@loginOut');
},
    ['middleware' => [AdminMiddleware::class]]);

Router::addGroup('/api/v1/mine/', function () {
    Router::post('create', 'App\Controller\v1\MineController@create');
    Router::post('update', 'App\Controller\v1\MineController@update');
    Router::post('base_config', 'App\Controller\v1\MineController@mineBaseConfigGet');
    Router::post('base_config_save', 'App\Controller\v1\MineController@mineBaseConfigSave');
    Router::post('coin_create', 'App\Controller\v1\MineController@coinCreate');
    Router::post('coin_update', 'App\Controller\v1\MineController@coinUpdate');
    Router::get('mine_list', 'App\Controller\v1\MineController@mineList');
    Router::get('coin_list', 'App\Controller\v1\MineController@coinList');
    Router::post('sw_create', 'App\Controller\v1\MineController@separateWarehouseCreate');
    Router::post('sw_update', 'App\Controller\v1\MineController@separateWarehouseUpdate');
    Router::get('sw_del', 'App\Controller\v1\MineController@separateWarehouseDel');
    Router::get('sw_list', 'App\Controller\v1\MineController@separateWarehouseList');
    Router::get('warehouse/record/list', 'App\Controller\v1\UserController@getUserWarehouseRecord');
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
    Router::post(
        'dynamic/small_income_config_create',
        'App\Controller\v1\DynamicController@smallIncomeConfigCreate'
    );
    Router::post(
        'dynamic/small_income_config_update',
        'App\Controller\v1\DynamicController@smallIncomeConfigUpdate'
    );
    Router::get(
        'dynamic/small_income_config_get',
        'App\Controller\v1\DynamicController@smallIncomeConfigGet'
    );
    Router::post(
        'dynamic/exclude_user_create',
        'App\Controller\v1\DynamicController@excludeUsersCreate'
    );
    Router::get(
        'dynamic/exclude_user_get',
        'App\Controller\v1\DynamicController@excludeUsersGet'
    );
    Router::get(
        'dynamic/small_income_list',
        'App\Controller\v1\DynamicController@smallIncomeList'
    );
    Router::get(
        'static/income_list',
        'App\Controller\v1\StaticController@staticIncomeList'
    );
    Router::get(
        'report/income_list',
        'App\Controller\v1\MineController@incomeList'
    );
},
    ['middleware' => [AdminMiddleware::class]]);
