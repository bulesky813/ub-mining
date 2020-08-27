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

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\User\UserChangeAssetsRequest;
use App\Request\User\UserRelationRequest;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\DbConnection\Db;

class UserController extends AbstractController
{
    public function relation(UserRelationRequest $request, UserRelationService $urs)
    {
        $user_id = (int)$request->input('user_id', 0);
        $parent_id = (int)$request->input('parent_id', 0);
        Db::beginTransaction();
        try {
            $user = $urs->bind($user_id, $parent_id);
            Db::commit();
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function changeAssets(
        UserChangeAssetsRequest $request,
        UserAssetsService $uas,
        UserWarehouseService $uws,
        UserWarehouseRecordService $uwrs
    ) {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $separate_warehouse_sort = (int)$request->input('separate_warehouse_sort');
        $value = (string)$request->input('value');
        Db::beginTransaction();
        try {
            $user_warehouse = $uws->setUserWarehouse($user_id, $coin_symbol, $separate_warehouse_sort, $value);
            $user_warehouse_record = $uwrs->record([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'sort' => $separate_warehouse_sort,
                'value_before' => bcsub((string)$user_warehouse->assets, $value),
                'num' => $value
            ]);
            Db::commit();
            return $this->success($user_warehouse_record->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }
}
