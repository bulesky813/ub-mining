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

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    public function success(array $data, string $message = '', int $code = 0)
    {
        return $this->response->json([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ]);
    }

    public function error(string $message, int $code = 500, array $data = [])
    {
        return $this->response->json([
            'code' => $code,
            'data' => $data,
            'message' => '网络错误'
        ]);
    }
}
