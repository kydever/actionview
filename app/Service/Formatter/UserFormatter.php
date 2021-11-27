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

use App\Model\User;
use Han\Utils\Service;

class UserFormatter extends Service
{
    public function base(User $model)
    {
        return [
            'id' => $model->id,
            'email' => $model->email,
            'first_name' => $model->first_name,
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
}
