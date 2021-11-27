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
namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\UserAuth;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PrivilegeMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        $privileges = $dispatched->handler->options['options'][self::class] ?? null;
        if ($privileges === null) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '权限未明确');
        }

        $user = UserAuth::instance()->build()->getUser();
        if (! $user->mustContainsAccesses($privileges)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }
        return $handler->handle($request);
    }
}
