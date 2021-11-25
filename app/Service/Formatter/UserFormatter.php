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
        //         avatar: "f95033df5f25c124771aafe58ef497c8"
        // created_at: "2017-07-14 09:16:35"
        // department: "ssssss"
        // email: "hongzhong@actionview.cn"
        // first_name: "红中"
        // id: "59681b7310e411208a0d8eb7"
        // last_login: {date: "2021-11-25 19:41:08.000000", timezone_type: 3, timezone: "Asia/Chongqing"}
        // latest_access_project: "demo"
        // position: ""
        // updated_at: "2021-11-25 19:41:08"
        return [
            'id' => $model->id,
            'email' => $model->email,
            'first_name' => $model->first_name,
            // 'avatar' => '',
            'permissions' => $model->permissions,
            // 'latest_access_project' => 'demo',
            // 'latest_access_url' => '/project/boba/summary'
        ];
    }
}
