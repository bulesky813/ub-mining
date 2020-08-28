<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Income\DynamicBigIncomeConfigRequest;
use App\Services\Income\DynamicBigIncomeConfigService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class DynamicController extends AbstractController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    public function bigIncomeConfigCreate(
        DynamicBigIncomeConfigRequest $request,
        DynamicBigIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->configCreate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function bigIncomeConfigUpdate(
        DynamicBigIncomeConfigRequest $request,
        DynamicBigIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->configUpdate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function bigIncomeConfigDel(
        DynamicBigIncomeConfigRequest $request,
        DynamicBigIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->configDel($params);
            return $this->success([]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function bigIncomeConfigGet(
        DynamicBigIncomeConfigRequest $request,
        DynamicBigIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->getConfig($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
