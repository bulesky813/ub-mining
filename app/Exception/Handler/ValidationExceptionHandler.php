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

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof ValidationException) {
            $data = json_encode([
                'code' => 500,
                'message' => $throwable->validator->errors()->first(),
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
            $this->stopPropagation();
            return $response->withHeader(
                'Content-type',
                'application/json;charset=utf-8'
            )->withStatus(200)->withBody(new SwooleStream($data));
        }
        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        if ($throwable instanceof ValidationException) {
            return true;
        } else {
            return false;
        }
    }
}
