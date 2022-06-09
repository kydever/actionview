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
use App\Model\Project;
use App\Model\User;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
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

if (! function_exists('get_project')) {
    function get_project(): ?Project
    {
        return ProjectAuth::instance()->build()->getCurrent();
    }
}

if (! function_exists('get_project_key')) {
    function get_project_key(): string
    {
        $project = get_project();
        if (empty($project)) {
            throw new BusinessException(ErrorCode::PROJECT_NOT_EXIST);
        }

        return $project->key;
    }
}

if (! function_exists('get_user')) {
    function get_user(): ?User
    {
        return UserAuth::instance()->build()->getUser();
    }
}

if (! function_exists('get_user_id')) {
    function get_user_id(): int
    {
        return UserAuth::instance()->build()->getUserId();
    }
}

if (! function_exists('format_uploaded_path')) {
    function format_uploaded_path(string $path)
    {
        return date('Y/m/d') . '/' . trim($path, '/');
    }
}

if (! function_exists('format_id_to_string')) {
    function format_id_to_string(array $items): array
    {
        $result = [];
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $result[$key] = format_id_to_string($item);
                continue;
            }

            if ($key === 'id' && ! is_array($item)) {
                $result[$key] = (string) $item;
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }
}

if (! function_exists('array_item_to_int')) {
    function array_item_to_int(array $items): array
    {
        $result = [];
        foreach ($items as $key => $item) {
            $result[$key] = (int) $item;
        }
        return $result;
    }
}

/*
 * 将 Unix 时间戳转换为日期.
 */
if (! function_exists('format')) {
    function format(string $format, int &$timestamp): void
    {
        $timestamp = date($format, $timestamp);
    }
}

/*
 * 将 Unix 时间戳转换为日期(年-月-日).
 */
if (! function_exists('formatDate')) {
    function formatDate(int &$timestamp): void
    {
        format('Y-m-d', $timestamp);
    }
}

/*
 * 将 Unix 时间戳转换为日期(年-月-日 时:分:秒).
 */
if (! function_exists('formatDateTime')) {
    function formatDateTime(int &$timestamp): void
    {
        format('Y-m-d H:i:s', $timestamp);
    }
}
