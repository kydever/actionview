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
use App\Model\Project;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use function Han\Utils\sort;

class ProjectDao extends Service
{
    public function first(int $id, bool $throw = false)
    {
        $model = Project::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::PROJECT_NOT_EXIST);
        }
        return $model;
    }

    public function create(string $key, string $name, string $description, array $creator, array $principal)
    {
        $model = new Project();
        $model->key = $key;
        $model->name = $name;
        $model->description = $description;
        $model->creator = $creator;
        $model->principal = $principal;
        $model->category = 1;
        $model->status = Project::ACTIVE;
        $model->save();

        return $model;
    }

    public function exists(string $key): bool
    {
        return Project::query()->where('key', $key)->exists();
    }

    public function firstByKey(string $key, bool $throw = false): ?Project
    {
        $model = Project::query()->where('key', $key)->first();
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::PROJECT_NOT_EXIST);
        }

        return $model;
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Project[]
     */
    public function findByKeys(array $keys)
    {
        return Project::query()->whereIn('key', $keys)->get();
    }

    /**
     * @param $input = [
     *     'principal_id' => 1,
     *     'status' => 'active',
     *     'key_or_name' => '',
     * ]
     */
    public function search(array $input, int $offset, int $limit)
    {
        $query = Project::query();
        if (! empty($input['principal_id'])) {
            $query->where('principal->id', $input['principal_id']);
        }

        if (! empty($input['status']) && $input['status'] !== Project::ALL) {
            $query = $query->where('status', $input['status']);
        }

        if (! empty($input['key_or_name'])) {
            $name = $input['key_or_name'];
            $query->where(static function ($query) use ($name) {
                $query->where('key', 'like', '%' . $name . '%')->orWhere('name', 'like', '%' . $name . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        return $this->factory->model->pagination($query, $offset, $limit);
    }

    /**
     * @param $input = [
     *     'keys' => $keys,
     *     'key_or_name' => $name,
     *     'status' => $status,
     * ]
     * @return Collection|Project[]
     */
    public function find(array $input = [], int $limit = 10)
    {
        $keys = $input['keys'] ?? [];
        $query = Project::query();
        if (! empty($keys)) {
            $query->whereIn('key', $keys);
        }
        if (! empty($input['key_or_name'])) {
            $name = $input['key_or_name'];
            $query->where(static function ($query) use ($name) {
                $query->where('key', 'like', '%' . $name . '%')->orWhere('name', 'like', '%' . $name . '%');
            });
        }
        if (! empty($input['status'])) {
            $status = $input['status'];
            if ($status != 'all') {
                $query = $query->where('status', $status);
            }
        }
        $models = $query->limit($limit)->get();
        if ($models->isEmpty()) {
            return $models;
        }
        $models = sort($models->getDictionary(), static function (Project $model) use ($keys) {
            return PHP_INT_MAX - ($keys[$model->key] ?? 0);
        });

        return new Collection($models);
    }

    public function findAllProjectKeys()
    {
        $models = Project::query()->get(['key', 'created_at']);
        $result = [];
        /** @var Project $model */
        foreach ($models as $model) {
            $result[$model->key] = $model->created_at->getTimestamp();
        }
        return $result;
    }
}
