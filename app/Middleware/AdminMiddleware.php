<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Base\BaseRedisService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Services\Admin\AdminUsersService;

class AdminMiddleware implements MiddlewareInterface
{
    use BaseRedisService;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(
        ContainerInterface $container,
        HttpResponse $response,
        RequestInterface $request
    ) {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routes_name = $request->getUri()->getPath();

        if (!in_array($routes_name, $this->noNeedToken())) {
            if (!$request->hasHeader('Access-Token')) {
                return $this->throwResponse('未登录', 406);
            }
            $token = $request->getHeader('Access-Token');
            $token = $token[0];
            $exist = $this->redis()->get(AdminUsersService::ADMIN_USER_LOGIN_TOKEN . $token);
            if (!$exist) {
                return $this->throwResponse('未登录', 405);
            }
        }

        return $handler->handle($request);
    }

    private function throwResponse($msg, $code = 1)
    {
        return $this->response->json(
            [
                'code' => $code == 1 ? 1 : $code,
                'message' => $msg,
                'data' => [],
            ]
        );
    }

    private function noNeedToken()
    {
        //不需要带token的路由名
        $noNeedToken = [
            '/api/v1/admin/login',
        ];
        return $noNeedToken;
    }
}
