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

use App\Constants\Permission;
use App\Customization\Eloquent\State;
use App\Customization\Eloquent\StateProperty;
use App\Service\Context\GroupContext;
use App\Service\Dao\ConfigStateDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;

/**
 * TODO: 不懂为什么叫这个名字.
 */
class ProviderService extends Service
{
    public function getUserList(string $key): array
    {
        $projects = di()->get(UserGroupProjectDao::class)->findByProjectKey($key);

        $userIds = [];
        $groupIds = [];
        foreach ($projects as $project) {
            $project->isGroup() ? $groupIds[] = $project->ug_id : $userIds[] = $project->ug_id;
        }

        if ($groupIds) {
            $groups = GroupContext::instance()->find($groupIds);
            foreach ($groups as $group) {
                $userIds = array_merge($userIds, $group->users);
            }
        }

        $userIds = array_values(array_unique($userIds));

        $models = di()->get(UserDao::class)->findMany($userIds);

        return di()->get(UserFormatter::class)->formatSmalls($models);
    }

    public function getAssignedUsers(string $key)
    {
        $userIds = di()->get(AclService::class)->getUserIdsByPermission(Permission::ISSUE_ASSIGNED, $key);

        $models = di()->get(UserDao::class)->findMany($userIds);

        return di()->get(UserFormatter::class)->formatSmalls($models);
    }

    /**
     * get state list.
     *
     * @param string $project_key
     * @param array $fields
     * @param mixed $key
     * @return collection
     */
    public static function getStateList($key, $fields = [])
    {
        $states = di()->get(ConfigStateDao::class)->findOrByProjectKey($key);

        $stateProperty = StateProperty::Where('project_key', $project_key)->first();
        if ($stateProperty) {
            if ($sequence = $stateProperty->sequence) {
                $func = function ($v1, $v2) use ($sequence) {
                    $i1 = array_search($v1['_id'], $sequence);
                    $i1 = $i1 !== false ? $i1 : 998;
                    $i2 = array_search($v2['_id'], $sequence);
                    $i2 = $i2 !== false ? $i2 : 999;
                    return $i1 >= $i2 ? 1 : -1;
                };
                usort($states, $func);
            }
        }

        return $states;
    }
}
