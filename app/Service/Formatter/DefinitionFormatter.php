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

use App\Model\OswfDefinition;
use Han\Utils\Service;

class DefinitionFormatter extends Service
{
    public function base(OswfDefinition $model)
    {
        return [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'name' => $model->name,
            'latest_modifier' => $model->latest_modifier,
            'latest_modified_time' => $model->latest_modified_time,
            'steps' => $model->steps,
            'state_ids' => $model->state_ids,
            'screen_ids' => $model->screen_ids,
            'contents' => $model->contents,
        ];
    }
}
