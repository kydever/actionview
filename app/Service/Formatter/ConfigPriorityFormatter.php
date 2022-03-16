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

use App\Model\ConfigPriority;
use Han\Utils\Service;

class ConfigPriorityFormatter extends Service
{
    public function base(ConfigPriority $model): array
    {
        return [
            'id' => $model->id,
            'color' => $model->color,
            'description' => $model->description,
            'key' => $model->key,
            'name' => $model->name,
            'sn' => $model->sn,
            'default' => $model->default,
        ];
    }

    public function formatList($models): array
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
