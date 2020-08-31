<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Admin\AdminUsersRequest;
use App\Services\Admin\AdminUsersService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class AdminController extends AbstractController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    public function login(
        AdminUsersRequest $request,
        AdminUsersService $service
    ) {
        try {
            $params = $request->all();
            $user = $service->login($params);
            return $this->success($user);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function loginOut(
        AdminUsersRequest $request,
        AdminUsersService $service
    ) {
        try {
            $params = $request->all();
            $token = $request->getHeader('Access-Token');
            $user = $service->loginOut($token[0]);
            return $this->success([]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
