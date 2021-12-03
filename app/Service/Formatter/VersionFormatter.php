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

use App\Model\Version;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class VersionFormatter extends Service
{
    public function base(Version $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'project_key' => $model->project_key,
            'start_time' => $model->start_time,
            'end_time' => $model->end_time,
            'creator' => $model->creator,
            'status' => $model->status,
            'description' => $model->description,
            'released_time' => $model->released_time,
            'created_at' => $model->created_at->toDateTimeString(),
            'updated_at' => $model->updated_at->toDateTimeString(),
        ];
    }

    public function formatList(Collection $models)
    {
        $result = [];
        /** @var Version[] $models */
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }
        return $result;
    }
}
