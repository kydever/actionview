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
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Version;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class VersionDao extends Service
{
    public function first(int $id, bool $throw = false): ?Version
    {
        $model = Version::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::VERSION_NOT_EXIST);
        }
        return $model;
    }

    public function firstByName(string $key, string $name): ?Version
    {
        return Version::query()->where('project_key', $key)->where('name', $name)->first();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Version[]
     */
    public function findByProjectKey(string $key)
    {
        return Version::query()->where(['project_key' => $key])
            ->orderBy('status', 'desc')
            ->orderBy('released_time', 'desc')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function index(string $key, int $offset, int $limit): array
    {
        $query = Version::query()
            ->where(['project_key' => $key])
            ->orderBy('status', 'desc')
            ->orderBy('released_time', 'desc')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc');
        return $this->factory->model->pagination($query, $offset, $limit);
    }

    /**
     * @return Collection<int, Version>
     */
    public function getByProjectKey(string $projectKey): Collection
    {
        return Version::where('project_key', $projectKey)
            ->orderBy('created_at', 'desc')
            ->get(['name']);
    }

    public function firstByProjectKey(string $projectKey, string $status): Version
    {
        return Version::where('project_key', $projectKey)
            ->where('status', $status)
            ->orderBy('name')
            ->first();
    }
}
