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
            $return = $data->toArray();
            if (!empty($return) && !isset($params_body['background'])) {
                foreach ($return as $k => &$v) {
                    $v['sid'] = $v['id'];
                    $v['num'] = bcmul((string)$v['num'], '1', 2);
                    $v['percent'] = bcmul((string)$v['percent'], '1', 2);
                    $v['today_income'] = bcmul((string)$v['today_income'], '1', 2);
                }
            }
            return $this->success($return);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
