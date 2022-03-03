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
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\ProjectAuth;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di(?string $id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('issue_key')) {
    function issue_key(string $key): string
    {
        return $key;
    }
}

if ( ! function_exists ( 'get_project' ) ) {
    /**
     * Get a project instance.
     *
     * @return \App\Model\Project|null
     */
    function get_project()
    {
        return ProjectAuth::instance()->build()->getCurrent();
    }
}


if ( ! function_exists ( 'get_project_key' ) ) {

    /**
     * Get a project key.
     *
     * @return string
     */
    function get_project_key()
    {
        $project = get_project();
        if ( empty ( $model ) ) {
            throw new BusinessException ( ErrorCode::PROJECT_NOT_EXIST );
        }

        return $project->key;
    }
}
