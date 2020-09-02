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

use App\Exception\UserUniqueException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
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
        if ($throwable instanceof UserUniqueException) {
            $this->stopPropagation();
            return $response->withHeader('Content-type', 'application/json;charset=utf-8')
                ->withStatus(200)
                ->withBody(new SwooleStream(json_encode([
                    'code' => 0,
                    'message' => $throwable->getMessage(),
                    'data' => []
                ], JSON_UNESCAPED_UNICODE)));
        } else {
            $this->logger->error(sprintf(
                '%s[%s] in %s',
                $throwable->getMessage(),
                $throwable->getLine(),
                $throwable->getFile()
            ));
            $this->logger->error($throwable->getTraceAsString());
            return $response->withHeader(
                'Server',
                'Hyperf'
            )->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
        }
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
