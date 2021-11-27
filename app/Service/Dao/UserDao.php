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
        return User::query()->where('invalid_flag', UserConstant::INVALID_FLAG)
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
}
