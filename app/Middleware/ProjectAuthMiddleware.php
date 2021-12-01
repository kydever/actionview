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
use App\Service\ProjectAuth;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProjectAuthMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        $key = $dispatched->params['project_key'] ?? null;
        if (empty($key)) {
            throw new BusinessException(ErrorCode::PROJECT_KEY_CANNOT_BE_EMPTY);
        }

        $projectAuth = ProjectAuth::instance([$key]);
        if (strtoupper($request->getMethod()) === 'GET' && ! $projectAuth->isSYS()) {
            if (! $projectAuth->isActive()) {
                throw new BusinessException(ErrorCode::PROJECT_ARCHIVED);
            }
        }

        return $handler->handle($request);
    }
}
