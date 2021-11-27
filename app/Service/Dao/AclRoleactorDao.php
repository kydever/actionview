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

use App\Model\AclRoleactor;
use Han\Utils\Service;

class AclRoleactorDao extends Service
{
    /**
     * @return AclRoleactor[]|\Hyperf\Database\Model\Collection
     */
    public function findByGroupId(int $groupId)
    {
        return AclRoleactor::query()->whereRaw('JSON_CONTAINS(group_ids, ?, ?)', [$groupId, '$'])->get();
    }
}
