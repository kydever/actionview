<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Exception\CircularDependencyException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    protected Response $response;

    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(Response::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        switch (true) {
            case $throwable instanceof HttpException:
                return $this->response->handleException($throwable);
            case $throwable instanceof BusinessException:
                $this->logger->warning(format_throwable($throwable));
                return $this->response->fail($throwable->getCode(), $throwable->getMessage());
            case $throwable instanceof CircularDependencyException:
                $this->logger->error($throwable->getMessage());
                return $this->response->fail(ErrorCode::SERVER_ERROR, $throwable->getMessage());
            case $throwable instanceof ValidationException:
                // 兼容前端错误码
                $code = $throwable->validator->errors()->first();
                if (is_numeric($code)) {
                    $code = (int) $code;
                    $message = ErrorCode::getMessage($code);
                } else {
                    $message = $code;
                    $code = ErrorCode::SERVER_ERROR;
                }
                $this->logger->error(sprintf('[%s] %s', $code, $message));
                return $this->response->fail($code, $message);
        }

        $this->logger->error(format_throwable($throwable));

        return $this->response->fail(ErrorCode::SERVER_ERROR, 'Server Error');
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
