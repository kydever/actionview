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
use App\Constants\UserConstant;
use App\Exception\BusinessException;
use App\Model\User;
use Han\Utils\Service;
use Hyperf\Database\Model\Builder;

class UserDao extends Service
{
    public function first(int $id, bool $throw = false): ?User
    {
        $model = User::findFromCache($id);
        if ($throw && empty($model)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXISTS);
        }
        return $model;
    }

    public function firstByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|User[]
     */
    public function findByKeyword(string $keyword)
    {
        return User::query()->where('invalid_flag', UserConstant::VALID_FLAG)
            ->where(static function (Builder $query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->limit(10)
            ->get();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|User[]
     */
    public function findMany(array $ids)
    {
        return User::findManyFromCache($ids);
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'ids' => [],
     *     'directory' => '',
     * ]
     */
    public function find(array $input, int $offset = 0, int $limit = 10)
    {
        $query = User::query()->where('email', '<>', '')
            ->where('id', '<>', 1);

        if ($name = $input['name'] ?? null) {
            $query->where(function ($query) use ($name) {
                $query->where('email', 'like', '%' . $name . '%')->orWhere('name', 'like', '%' . $name . '%');
            });
        }

        if (is_array($input['ids'] ?? null)) {
            $query->whereIn('id', $input['ids']);
        }

        if ($directory = $input['directory'] ?? null) {
            $query->where('directory', $directory);
        }

        $query->orderBy('id');

        return $this->factory->model->pagination($query, $offset, $limit);
    }
}
