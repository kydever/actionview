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

use App\Model\ConfigResolution;
use Han\Utils\Service;

class ConfigResolutionFormatter extends Service
{
    public function base(ConfigResolution $model): array
    {
        return [
            'id' => $model->id,
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
            $results[] = $model;
        }

        return $results;
    }
}
