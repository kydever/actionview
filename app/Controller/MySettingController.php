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
namespace App\Controller;

use App\Service\UserAuth;

class MySettingController extends Controller
{
    public function show()
    {
        var_dump(UserAuth::instance()->getUserId());
        return $this->response->success();
        // $data = [];
        //
        // $user = Sentinel::findById($this->user->id);
        // if (!$user)
        // {
        //     throw new \UnexpectedValueException('the user is not existed.', -15000);
        // }
        // $data['accounts'] = $user;
        //
        // $user_setting = UserSetting::where('user_id', $this->user->id)->first();
        // if ($user_setting && isset($user_setting->notifications))
        // {
        //     $data['notifications'] = $user_setting->notifications;
        // }
        // else
        // {
        //     $data['notifications'] = [ 'mail_notify' => true ];
        // }
        //
        // if ($user_setting && isset($user_setting->favorites))
        // {
        //     $data['favorites'] = $user_setting->favorites;
        // }
        //
        // return Response()->json([ 'ecode' => 0, 'data' => $data ]);
    }
}
