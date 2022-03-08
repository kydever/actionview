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

use App\Model\ConfigScreen;
use Han\Utils\Service;

class ConfigScreenFormatter extends Service
{
    public function base(ConfigScreen $model)
    {
        return [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'name' => $model->name,
            'description' => $model->description,
            'schema' => $model->schema,
            'field_ids' => $model->field_ids,
        ];
    }

    public function formatList($models)
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }

        return $result;
    }
}
