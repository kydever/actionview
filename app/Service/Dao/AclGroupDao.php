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

use App\Model\AclGroup;
use Han\Utils\Service;

class AclGroupDao extends Service
{
    /**
     * @return AclGroup[]|\Hyperf\Database\Model\Collection
     */
    public function findByUserId(int $userId)
    {
        return AclGroup::query()->whereRaw('JSON_CONTAINS(users, ?)', $userId)->get();
    }
}
