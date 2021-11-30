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

use App\Model\Epic;
use Han\Utils\Service;

class EpicDao extends Service
{
    /**
     * @return Epic[]|\Hyperf\Database\Model\Collection
     */
    public function getEpicList(string $key)
    {
        return Epic::query()->where('project_key', $key)->orderBy('sn')->get();
    }
}
