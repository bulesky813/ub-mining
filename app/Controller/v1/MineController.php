<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Mine\MineCoinRequest;
use App\Request\Mine\MinePoolRequest;
use App\Request\Mine\SeparateWarehouseRequest;
use App\Services\Mine\MinePoolService;
use App\Services\Mine\MineCoinService;
use App\Services\Mine\SeparateWarehouseService;
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
    public function mineList(MinePoolRequest $request, MinePoolService $service)
    {
        try {
            $params = $request->all();
            $data = $service->mineList($params);
            return $this->success($data->toArray());
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
        SeparateWarehouseService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->separateWarehouseList($params);
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
