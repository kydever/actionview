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
namespace App\Service;

use App\Model\AclRoleactor;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class AclRoleactorService extends Service
{
    /**
     * @return Collection<int, AclRoleactor>
     */
    public function getByRoleId(int $roleId)
    {
        return AclRoleactor::query()
            ->where('role_id', $roleId)
            ->get();
    }
}
