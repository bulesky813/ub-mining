<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Income\DynamicBigIncomeConfigRequest;
use App\Request\Income\DynamicSmallIncomeConfigRequest;
use App\Request\Income\DynamicSmallIncomeRequest;
use App\Request\Income\ExcludeUsersRequest;
use App\Services\Income\DynamicBigIncomeConfigService;
use App\Services\Income\DynamicSmallIncomeConfigService;
use App\Services\Income\DynamicSmallIncomeService;
use App\Services\Income\ExcludeRewardsUsersService;
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
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function smallIncomeConfigCreate(
        DynamicSmallIncomeConfigRequest $request,
        DynamicSmallIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->configCreate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function smallIncomeConfigUpdate(
        DynamicSmallIncomeConfigRequest $request,
        DynamicSmallIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->configUpdate($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function smallIncomeConfigGet(
        DynamicSmallIncomeConfigRequest $request,
        DynamicSmallIncomeConfigService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->getConfig($params);
            return $this->success($data->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function excludeUsersCreate(
        ExcludeUsersRequest $request,
        ExcludeRewardsUsersService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->excludeUsersCreate($params);
            return $this->success([]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function excludeUsersGet(
        ExcludeUsersRequest $request,
        ExcludeRewardsUsersService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->excludeUsersGet($params);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function smallIncomeList(
        DynamicSmallIncomeRequest $request,
        DynamicSmallIncomeService $service
    ) {
        try {
            $params = $request->all();
            $data = $service->getList($params);
            $data = $data->toArray();
            foreach ($data as $k => &$v) {
                $v['sid'] = $v['id'];
            }
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
