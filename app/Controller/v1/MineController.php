<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Income\IncomeStatisticsRequest;
use App\Request\Mine\MineCoinRequest;
use App\Request\Mine\MinePoolRequest;
use App\Request\Mine\SeparateWarehouseRequest;
use App\Services\Income\IncomeStatisticsService;
use App\Services\Mine\MinePoolService;
use App\Services\Mine\MineCoinService;
use App\Services\Income\ExcludeRewardsUsersService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class MineController extends AbstractController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    /**
     * 创建矿池
     * @param MinePoolRequest $request
     * @param MineService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(MinePoolRequest $request, MinePoolService $service)
    {
        try {
            $params = $request->all();
            $mine = $service->createMine($params);
            return $this->success($mine);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 更新矿池
     * @param MinePoolRequest $request
     * @param MineService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(MinePoolRequest $request, MinePoolService $service)
    {
        try {
            $params = $request->all();
            $mine = $service->updateMine($params);
            return $this->success($mine);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinCreate(MineCoinRequest $request, MineCoinService $service)
    {
        try {
            $params = $request->all();
            $data = $service->coinCreate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinUpdate(MineCoinRequest $request, MineCoinService $service)
    {
        try {
            $params = $request->all();
            $data = $service->coinUpdate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 币列表
     * @param MineCoinRequest $request
     * @param MineCoinService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function coinList(MineCoinRequest $request, MineCoinService $service)
    {
        try {
            $params = $request->all();
            $data = $service->coinList($params);
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 矿池列表
     * @param MinePoolRequest $request
     * @param MinePoolService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function mineList(
        MinePoolRequest $request,
        MinePoolService $service,
        UserWarehouseService $us,
        SeparateWarehouseService $sws
    ) {
        try {
            $params = $request->all();
            $search_user = isset($params['user_id'])?$params['user_id']:0;
            unset($params['user_id']);
            $data = $service->mineList($params);
            $data = $data->toArray();
            //查询收益率
            foreach ($data as $k => &$v) {
                if ($search_user > 0) {
                    //最大仓
                    $max_sort = $us->maxWarehouseSort((int)$search_user, $v['coin_symbol']);
                    $v['rate'] = sprintf(
                        "%.2f",
                        $sws->getUserMaxSortRate($v['coin_symbol'], $max_sort)
                    );
                } else {
                    $v['rate'] = '0.00';
                }
            }
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 分仓添加
     * @param SeparateWarehouseRequest $request
     * @param SeparateWarehouseService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function separateWarehouseCreate(
        SeparateWarehouseRequest $request,
        SeparateWarehouseService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->separateWarehouseCreate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 分仓修改
     * @param SeparateWarehouseRequest $request
     * @param SeparateWarehouseService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function separateWarehouseUpdate(
        SeparateWarehouseRequest $request,
        SeparateWarehouseService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->separateWarehouseUpdate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除分仓
     * @param SeparateWarehouseRequest $request
     * @param SeparateWarehouseService $service
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function separateWarehouseDel(
        SeparateWarehouseRequest $request,
        SeparateWarehouseService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->separateWarehouseDel($params);
            return $this->success([]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function separateWarehouseList(
        SeparateWarehouseRequest $request,
        SeparateWarehouseService $service,
        UserWarehouseService $uws,
        UserWarehouseRecordService $uwrs
    ) {
        try {
            $params = $request->all();
            $user_id = (int)($params['user_id'] ?? 0);
            $coin_symbol = (string)($params['coin_symbol'] ?? '');
            $data = $service->separateWarehouseList($params)->toArray();
            $user_warehouse_list = collect([]);
            $today_revoke_record = null;
            if ($user_id) {
                $today_revoke_record = $uwrs->todayRevoke($user_id);
                $user_warehouse_list = $uws->userWarehouse($user_id, $coin_symbol);
            }
            foreach ($data as $key => $separate_warehouse) {
                $user_warehouse = $user_warehouse_list
                    ->get($separate_warehouse['sort'] - 1, new \stdClass());
                $user_assets = (string)($user_warehouse->assets ?? '0');
                $warehouse_assets = bcsub((string)$separate_warehouse['high'], $user_assets);
                $separate_warehouse['allow_add'] = 0;
                if (bccomp($warehouse_assets, '0') > 0
                    && $separate_warehouse['sort'] <= $user_warehouse_list->count() + 1) {
                    $separate_warehouse['allow_add'] = 1;
                }
                $separate_warehouse['allow_sub'] = 0;
                if ($separate_warehouse['sort'] == $user_warehouse_list->count()) {
                    $separate_warehouse['allow_sub'] = 1;
                }

                $data[$key] = $separate_warehouse;
            }
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function incomeList(
        IncomeStatisticsRequest $request,
        IncomeStatisticsService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->getList($params);
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function mineBaseConfigGet(
        MinePoolRequest $request,
        MinePoolService $service,
        ExcludeRewardsUsersService $erus
    ) {
        try {
            $params = $request->all();
            $data = $service->mineBaseConfigGet($params);
            $data = $data->toArray();
            $data2 = $erus->excludeUsersGet($params);
            $data = array_merge($data, $data2);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function mineBaseConfigSave(
        MinePoolRequest $request,
        MinePoolService $service,
        ExcludeRewardsUsersService $erus
    ) {
        try {
            $params = $request->all();
            $data1 = $service->mineBaseConfigSave($params);
            $erus->excludeUsersCreate($params);
            $data2 = $erus->excludeUsersGet($params);
            $data1 = $data1->toArray();
            $data1 = array_merge($data1, $data2);
            return $this->success($data1);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinSync(
        MineCoinRequest $request,
        MineCoinService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->coinSync($params);
            return $this->success([]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
