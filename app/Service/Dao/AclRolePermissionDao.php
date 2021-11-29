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

use App\Constants\ProjectConstant;
use App\Model\AclRolePermission;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;

class AclRolePermissionDao extends Service
{
    #[Cacheable(prefix: 'acl:role:permission:default', ttl: 8640000)]
    public function getDefaultPermissions(): array
    {
        return $this->defaultPermissions();
    }

    #[CachePut(prefix: 'acl:role:permission:default', ttl: 8640000)]
    public function putDefaultPermissions(): array
    {
        return $this->defaultPermissions();
    }

    public function defaultPermissions(): array
    {
        $models = AclRolePermission::query()->where('project_key', ProjectConstant::SYS)->get();

        $result = [];
        /** @var AclRolePermission $model */
        foreach ($models as $model) {
            $result[$model->role_id] = $model->permissions;
        }
        return $result;
    }

    /**
     * @return AclRolePermission[]|\Hyperf\Database\Model\Collection
     */
    public function findByRoleIds(string $projectKey, array $roleIds)
    {
        return AclRolePermission::query()
            ->where('project_key', $projectKey)
            ->whereIn('role_id', $roleIds)
            ->get();
    }

    public function findDictionaryByRoleIds(string $projectKey, array $roleIds): array
    {
        $models = $this->findByRoleIds($projectKey, $roleIds);
        $result = [];
        foreach ($models as $model) {
            $result[$model->role_id] = $model;
        }
        return $result;
    }
}
