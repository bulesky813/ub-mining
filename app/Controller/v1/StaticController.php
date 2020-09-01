<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\Income\StaticIncomeRequest;
use App\Services\Income\StaticIncomeService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class StaticController extends AbstractController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    public function staticIncomeList(
        StaticIncomeRequest $request,
        StaticIncomeService $service
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
