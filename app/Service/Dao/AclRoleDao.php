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

use App\Model\AclRole;
use Han\Utils\Service;

class AclRoleDao extends Service
{
    /**
     * @return AclRole[]|\Hyperf\Database\Model\Collection
     */
    public function findOrByProjectKey(string $key)
    {
        return AclRole::query()->where('project_key', '$_sys_$')
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('id')
            ->get();
    }
}
