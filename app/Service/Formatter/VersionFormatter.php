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
            'created_at' => (string) $model->created_at,
            'updated_at' => (string) $model->updated_at,
        ];
    }

    /**
     * @param $counts => [1 => ['cnt' => 1, 'unresolved_cnt' => 1]],
     */
    public function formatList(Collection $models, array $counts = [])
    {
        $result = [];
        /** @var Version[] $models */
        foreach ($models as $model) {
            $item = $this->base($model);
            if ($cnt = $counts[$model->id] ?? null) {
                $item['all_cnt'] = $cnt['cnt'] ?? 0;
                $item['unresolved_cnt'] = $cnt['unresolved_cnt'] ?? 0;
            }
            $result[] = $item;
        }
        return $result;
    }
}
