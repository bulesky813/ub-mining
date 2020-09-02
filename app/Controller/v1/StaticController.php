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
            $params_body = $request->getParsedBody();
            $data = $service->getList($params);
            $data = $data->toArray();
            if (!empty($data) && !isset($params_body['background'])) {
                foreach ($data as $k => &$v) {
                    $v['sid'] = $v['id'];
                    $v['num'] = sprintf("%.2f", $v['num']);
                    $v['percent'] = sprintf("%.2f", $v['percent']);
                    $v['today_income'] = sprintf("%.2f", $v['today_income']);
                }
            }
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
