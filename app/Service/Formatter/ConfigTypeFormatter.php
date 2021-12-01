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

use App\Model\ConfigType;
use Han\Utils\Service;

class ConfigTypeFormatter extends Service
{
    public function small(ConfigType $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'abb' => $model->abb,
            'disabled' => (bool) $model->disabled,
            'default' => (bool) $model->default,
            'type' => $model->type == 'subtask' ? 'subtask' : 'standard',
        ];
    }
}
