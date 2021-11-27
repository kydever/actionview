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

use App\Model\SysSetting;
use Han\Utils\Service;

class SysSettingFormatter extends Service
{
    public function base(SysSetting $model)
    {
        $mailServer = $model->mailserver;
        if (! empty($mailServer['smtp']['password'])) {
            $mailServer['smtp']['password'] = '******';
        }
        return [
            'id' => $model->id,
            'properties' => $model->properties,
            'mailserver' => $mailServer,
            'sysroles' => $model->sysroles,
        ];
    }
}
