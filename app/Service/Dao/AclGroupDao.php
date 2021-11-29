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
use App\Constants\StatusConstant;
use App\Exception\BusinessException;
use App\Model\AclGroup;
use Han\Utils\Service;

class AclGroupDao extends Service
{
    public function first(int $id, bool $throw = false)
    {
        $model = AclGroup::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::GROUP_NOT_EXSIT);
        }

        return $model;
    }

    /**
     * @return AclGroup[]|\Hyperf\Database\Model\Collection
     */
    public function findMany(array $ids)
    {
        return AclGroup::findManyFromCache($ids);
    }

    /**
     * @return AclGroup[]|\Hyperf\Database\Model\Collection
     */
    public function all()
    {
        return AclGroup::query()->get();
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'directory' => '',
     *     'public_scope' => 1,
     *     'scale' => ['myprincipal', 1],
     * ]
     */
    public function find(array $input, int $offset, int $limit)
    {
        $query = AclGroup::query()->where('name', '<>', '');

        if ($name = $input['name'] ?? null) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($directory = $input['directory'] ?? null) {
            $query->where('directory', $directory);
        }

        if ($scope = $input['public_scope'] ?? null) {
            if ($scope == StatusConstant::SCOPE_PUBLIC) {
                $query->where('public_scope', '<>', StatusConstant::SCOPE_PRIVATE)->where('public_scope', '<>', StatusConstant::SCOPE_MEMBER);
            } elseif (in_array($scope, [StatusConstant::SCOPE_PRIVATE, StatusConstant::SCOPE_MEMBER])) {
                $query->where('public_scope', $scope);
            }
        }

        if ($scale = $input['scale'] ?? null) {
            [$scale, $userId] = $scale;
            $query = match ($scale) {
                'myprincipal' => $query->where('principal->id', $userId),
                'myjoin' => $query->whereRaw('JSON_CONTAINS(users, ?, ?)', [$userId, '&']),
                default => $query,
            };

            $query->where(static function ($query) use ($userId) {
                $query->where('principal->id', $userId)
                    ->orWhere(function ($query) {
                        $query->where('public_scope', '<>', '2')->whereRaw('JSON_CONTAINS(users, ?, ?)', [$userId, '&']);
                    });
            });
        }

        return $this->factory->model->pagination($query, $offset, $limit);
    }

    /**
     * @return AclGroup[]|\Hyperf\Database\Model\Collection
     */
    public function findByUserId(int $userId)
    {
        return AclGroup::query()->whereRaw('JSON_CONTAINS(users, ?, ?)', [$userId, '$'])->get();
    }
}
