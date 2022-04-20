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
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class IssueFormatter extends Service
{
    public function base(Issue $model, int $userId = 0)
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
            'attachments' => $model->attachments,
            'watchers' => $model->watchers ?: [],
            'comments_num' => $model->comments_num,
            'created_at' => $model->created_at->getTimestamp(),
            'updated_at' => $model->updated_at->getTimestamp(),
        ];
        if ($userId > 0) {
            $result['watching'] = in_array($userId, array_column($model->watchers ?: [], 'id'));
        }
        return array_replace($model->getData(), $result);
    }

    /**
     * @param Collection<int, Issue> $models
     */
    public function formatList(Collection $models, int $userId = 0): array
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model, $userId);
        }
        return $result;
    }
}
