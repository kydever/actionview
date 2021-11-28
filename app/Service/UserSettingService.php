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

use App\Model\User;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserSettingDao;
use App\Service\Formatter\UserFormatter;
use App\Service\Struct\Image;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\FilesystemOperator;

class UserSettingService extends Service
{
    #[Inject]
    protected UserSettingDao $dao;

    #[Inject]
    protected FilesystemOperator $file;

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

    public function setAvatar(string $data, User $user)
    {
        $dir = date('Y/m/d');

        $image = Image::makeFromBase64Data($data, BASE_PATH . '/runtime/' . $dir);

        $object = $image->toAvatarPath();

        $this->file->writeStream($path = $dir . '/' . uniqid() . '.' . $image->getExtension(), $stream = fopen($object, 'r+'));

        fclose($stream);

        $user->avatar = env('QINIU_DOMAIN') . '/' . $path;
        $user->save();

        return [
            'accounts' => di()->get(UserFormatter::class)->base($user),
        ];
    }
}
