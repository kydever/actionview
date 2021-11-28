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

use App\Service\Dao\UserDao;
use App\Service\Dao\UserSettingDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class UserSettingService extends Service
{
    #[Inject]
    protected UserSettingDao $dao;

    public function show(int $userId)
    {
        $user = di()->get(UserDao::class)->first($userId, true);
        $userSetting = $this->dao->first($userId);

        return [
            'notifications' => $userSetting?->notifications ?? ['mail_notify' => true],
            'favorites' => $userSetting?->favorites,
            'accounts' => di()->get(UserFormatter::class)->base($user),
        ];
    }

    public function setAvatar()
    {
        // $basename = md5(microtime() . $this->user->id);
        // $avatar_save_path = config('filesystems.disks.local.root', '/tmp') . '/avatar/';
        // if (! is_dir($avatar_save_path)) {
        //     @mkdir($avatar_save_path);
        // }
        // $filename = '/tmp/' . $basename;
        //
        // $data = $request->input('data');
        // if (! $data) {
        //     throw new \UnexpectedValueException('the uploaded avatar file can not be empty.', -15006);
        // }
        // file_put_contents($filename, base64_decode($data));
        //
        // $fileinfo = getimagesize($filename);
        // if ($fileinfo['mime'] == 'image/jpeg' || $fileinfo['mime'] == 'image/jpg' || $fileinfo['mime'] == 'image/png' || $fileinfo['mime'] == 'image/gif') {
        //     $size = getimagesize($filename);
        //     $width = $size[0];
        //     $height = $size[1];
        //     $scale = $width < $height ? $height : $width;
        //     $thumbnails_width = floor(150 * $width / $scale);
        //     $thumbnails_height = floor(150 * $height / $scale);
        //     $thumbnails_filename = $filename . '_thumbnails';
        //     if ($scale <= 150) {
        //         @copy($filename, $thumbnails_filename);
        //     } elseif ($fileinfo['mime'] == 'image/jpeg' || $fileinfo['mime'] == 'image/jpg') {
        //         $src_image = imagecreatefromjpeg($filename);
        //         $dst_image = imagecreatetruecolor($thumbnails_width, $thumbnails_height);
        //         imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $thumbnails_width, $thumbnails_height, $width, $height);
        //         imagejpeg($dst_image, $thumbnails_filename);
        //     } elseif ($fileinfo['mime'] == 'image/png') {
        //         $src_image = imagecreatefrompng($filename);
        //         $dst_image = imagecreatetruecolor($thumbnails_width, $thumbnails_height);
        //         imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $thumbnails_width, $thumbnails_height, $width, $height);
        //         imagepng($dst_image, $thumbnails_filename);
        //     } elseif ($fileinfo['mime'] == 'image/gif') {
        //         $src_image = imagecreatefromgif($filename);
        //         $dst_image = imagecreatetruecolor($thumbnails_width, $thumbnails_height);
        //         imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $thumbnails_width, $thumbnails_height, $width, $height);
        //         imagegif($dst_image, $thumbnails_filename);
        //     } else {
        //         @copy($filename, $thumbnails_filename);
        //     }
        //
        //     @rename($thumbnails_filename, $avatar_save_path . $basename);
        // } else {
        //     throw new \UnexpectedValueException('the avatar file type has errors.', -15007);
        // }
        //
        // $user = Sentinel::findById($this->user->id);
        // if (! $user) {
        //     throw new \UnexpectedValueException('the user is not existed.', -15000);
        // }
        // $user->fill(['avatar' => $basename])->save();
        //
        // return $this->show();
    }
}
