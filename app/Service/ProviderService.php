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

use App\Acl\Eloquent\Group;
use App\Service\Dao\UserGroupProjectDao;
use Cartalyst\Sentinel\Users\EloquentUser;
use Han\Utils\Service;

/**
 * TODO: 不懂为什么叫这个名字.
 */
class ProviderService extends Service
{
    public function getUserList(string $key): array
    {
        $projects = di()->get(UserGroupProjectDao::class)->findByProjectKey($key);

        $user_ids = [];
        $group_ids = [];
        foreach ($user_group_ids as $value) {
            if (isset($value->type) && $value->type === 'group') {
                $group_ids[] = $value->ug_id;
            } else {
                $user_ids[] = $value->ug_id;
            }
        }

        if ($group_ids) {
            $groups = Group::find($group_ids);
            foreach ($groups as $group) {
                $user_ids = array_merge($user_ids, isset($group->users) && $group->users ? $group->users : []);
            }
        }
        $user_ids = array_unique($user_ids);

        $user_list = [];
        $users = EloquentUser::find($user_ids);
        foreach ($users as $user) {
            if (isset($user->invalid_flag) && $user->invalid_flag === 1) {
                continue;
            }
            $user_list[] = ['id' => $user->id, 'name' => $user->first_name, 'email' => $user->email];
        }

        return $user_list;
    }
}
