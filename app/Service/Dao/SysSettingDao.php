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
namespace App\Service\Dao;

use App\Model\SysSetting;
use Han\Utils\Service;

class SysSettingDao extends Service
{
    public function first(int $id = 0): SysSetting
    {
        return SysSetting::findFromCache($id ?: 1);
    }
}
