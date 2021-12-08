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

use App\Model\ConfigState;
use Han\Utils\Service;

class StateFormatter extends Service
{
    public function base(ConfigState $model)
    {
        return [
            'id' => (string) $model->id,
            'key' => $model->key,
            'name' => $model->name,
            'sn' => $model->sn,
            'category' => $model->category,
        ];
    }
}
