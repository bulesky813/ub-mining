<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Mine\MineCoinRequest;
use App\Request\Mine\MinePoolRequest;
use App\Services\Mine\MineService;
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
    public function create(MinePoolRequest $request, MineService $service)
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
    public function update(MinePoolRequest $request, MineService $service)
    {
        try {
            $params = $request->all();
            $mine = $service->updateMine($params);
            return $this->success($mine);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinCreate(MineCoinRequest $request, MineService $service)
    {
        try {
            $params = $request->all();
            $data = $service->coinCreate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinUpdate(MineCoinRequest $request, MineService $service)
    {
        try {
            $params = $request->all();
            $data = $service->coinUpdate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function coinList()
    {
    }

    public function mineList(MinePoolRequest $request, MineService $service)
    {
        try {
            $params = $request->all();
            $data = $service->mineList($params);
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
