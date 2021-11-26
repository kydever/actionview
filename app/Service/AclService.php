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

use App\Service\Dao\AclGroupDao;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class AclService extends Service
{
    #[Inject]
    protected AclGroupDao $group;

    public function getBoundGroups(int $userId): array
    {
        $groups = [];
        $models = $this->group->findByUserId($userId);
        foreach ($models as $group) {
            $groups[] = ['id' => $group->id, 'name' => $group->name];
        }
        return $groups;
    }
}
