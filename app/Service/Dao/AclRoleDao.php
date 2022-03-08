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
use App\Constants\ProjectConstant;
use App\Exception\BusinessException;
use App\Model\AclRole;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class AclRoleDao extends Service
{
    public function first(int $id, bool $throw = false): ?AclRole
    {
        $model = AclRole::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::ROLE_NOT_EXISTS);
        }
        return $model;
    }

    /**
     * @return Collection<int, AclRole>
     */
    public function findOrByProjectKey(string $key)
    {
        return AclRole::query()->where('project_key', ProjectConstant::SYS)
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('id')
            ->get();
    }
}
