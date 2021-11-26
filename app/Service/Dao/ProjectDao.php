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

use App\Model\Project;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use function Han\Utils\sort;

class ProjectDao extends Service
{
    public function firstByKey(string $key): ?Project
    {
        return Project::query()->where('key', $key)->first();
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
     *     'keys' => $keys,
     *     'key_or_name' => $name,
     *     'status' => $status,
     * ]
     * @return Collection|Project[]
     */
    public function find(array $input = [], int $limit = 10)
    {
        $keys = $input['keys'] ?? [];
        $query = Project::query()->whereIn('key', $keys);
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
