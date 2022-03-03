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
namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use App\Model\Project;
use App\Service\ProjectAuth;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

abstract class Controller
{
    protected Response $response;

    protected RequestInterface $request;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(Response::class);
        $this->request = $container->get(RequestInterface::class);
    }

    /**
     * Get a Project instance.
     */
    public function getProject(): ?Project
    {
        return ProjectAuth::instance()->build()->getCurrent();
    }

    public function getProjectKey(): string
    {
        $model = $this->getProject();
        if (empty($model)) {
            throw new BusinessException(ErrorCode::PROJECT_NOT_EXIST);
        }

        return $model->key;
    }
}
