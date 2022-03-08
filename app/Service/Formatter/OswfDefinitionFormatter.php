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

class OswfDefinitionFormatter extends Service
{
    public function base(OswfDefinition $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'project_key' => $model->project_key,
            'description' => $model->description,
            'latest_modified_time' => $model->latest_modified_time,
            'latest_modifier' => $model->latest_modifier,
            'steps' => $model->steps,
        ];
    }

    public function formatList($models, ?array $usedMap = null): array
    {
        $result = [];
        /** @var OswfDefinition $model */
        foreach ($models as $model) {
            $item = $this->base($model);
            if (is_array($usedMap)) {
                $item['is_used'] = array_key_exists($model->id, $usedMap);
            }

            $result[] = $item;
        }

        return $result;
    }
}
