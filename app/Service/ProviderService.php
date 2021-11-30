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
use App\Model\ConfigState;
use App\Service\Context\GroupContext;
use App\Service\Dao\ConfigStateDao;
use App\Service\Dao\ConfigStatePropertyDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use function Han\Utils\sort;

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

    public function getStateList($key)
    {
        $states = di()->get(ConfigStateDao::class)->findOrByProjectKey($key);

        $property = di()->get(ConfigStatePropertyDao::class)->firstByProjectKey($key);
        if ($sequence = $property?->sequence) {
            $sequence = array_flip($sequence);
            $result = sort($states, static function (ConfigState $model) use ($sequence) {
                return -($sequence[$model->id] ?? 999);
            })->toArray();

            $states = new Collection($result);
        }

        $result = [];
        foreach ($states as $state) {
            $result[] = [
                'id' => $state->key ?: $state->id,
                'name' => trim($state->name),
                'category' => $state->category,
            ];
        }

        return $result;
    }
}
