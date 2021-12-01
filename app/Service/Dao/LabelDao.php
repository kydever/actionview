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

use App\Model\Label;
use Han\Utils\Service;

class LabelDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|Label[]
     */
    public function getLabelOptions(string $key)
    {
        return Label::query()->where('project_key', $key)->orderBy('id')->get();
    }
}
