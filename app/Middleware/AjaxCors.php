<?php
declare(strict_types=1);

namespace App\Middleware;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AjaxCors
 * @package App\Middleware
 */
class AjaxCors implements MiddlewareInterface
{

    /**
     *  Process an incoming server request.
     *  Processes an incoming server request in order to produce a response.
     *  If unable to produce the response itself, it may delegate to the provided
     *  request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Methods', '*')
            ->withHeader('Access-Control-Expose-Headers', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type,Access-Token,Access-Source,Access-Language');

        Context::set(ResponseInterface::class, $response);
        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }
        return $handler->handle($request);
    }
}