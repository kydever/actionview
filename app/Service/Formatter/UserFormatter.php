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
namespace App\Service\Formatter;

use App\Constants\UserConstant;
use App\Model\User;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class UserFormatter extends Service
{
    public function base(User $model)
    {
        return [
            'id' => $model->id,
            'email' => $model->email,
            'first_name' => $model->first_name,
            'directory' => $model->directory ?: 'self',
            // unactivated
            'status' => $model->isInvalid() ? UserConstant::INVALID : UserConstant::ACTIVE,
            'phone' => $model->phone ?: '',
            // 'avatar' => '',
            'permissions' => $model->permissions,
            // 'latest_access_url' => '/project/boba/summary'
        ];
    }

    public function small(User $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->first_name,
            'email' => $model->email,
        ];
    }

    /**
     * @param User[] $models
     */
    public function formatList(Collection $models): array
    {
        $result = [];
        foreach ($models as $model) {
            $item = $this->base($model);
            if ($model->relationLoaded('groups')) {
                $item['groups'] = $model->groups->columns('name')->toArray();
            }

            $result[] = $item;
        }

        return $result;
    }
}