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

use App\Model\Issue;
use App\Service\Dao\WatchDao;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class IssueFormatter extends Service
{
    public function base(Issue $model)
    {
        $result = [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'type' => (string) $model->type,
            'del_flg' => $model->del_flg,
            'resolution' => $model->resolution,
            'assignee' => format_id_to_string($model->assignee),
            'reporter' => format_id_to_string($model->reporter),
            'no' => $model->no,
            // 'watching' => di()->get(WatchDao::class)->exists($model->id, get_user_id()),
            // 'watchers' => di()->get(WatchFormatter::class)->formatList(di()->get(WatchDao::class)->get($model->id)),
            'attachments' => $model->attachments,
            'created_at' => $model->created_at->getTimestamp(),
            'updated_at' => $model->updated_at->getTimestamp(),
        ];

        return array_replace($model->getData(), $result);
    }

    /**
     * @param Collection<int, Issue> $models
     */
    public function formatListWithWatching(Collection $models, int $userId): array
    {
        $result = [];
        foreach ($models as $model) {
            $item = $this->base($model);
            $item['watching'] = in_array($userId, array_column($model->watchers ?: [], 'id'));
            $item['watchers'] = $model->watchers ?: [];
            $result[] = $item;
        }
        return $result;
    }

    public function formatList($models): array
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }
        return $result;
    }
}
