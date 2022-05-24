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

use App\Model\Activity;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ActivityFormatter extends Service
{
    public function base(Activity $model): array
    {
        return [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'data' => $model->data,
            'event_key' => $model->event_key,
            'issue' => $model->issue,
            'issue_id' => $model->issue_id,
            'user' => $model->user,
            'created_at' => strtotime((string) $model->created_at),
        ];
    }

    public function formatList(Collection $models): array
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
