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

use App\Service\Dao\AclRolePermissionDao;
use Han\Utils\Service;

class SysService extends Service
{
    public function flushCache()
    {
        di()->get(AclRolePermissionDao::class)->putDefaultPermissions();
    }
}
