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

use App\Model\Watch;
use Han\Utils\Service;

class WatchFormatter extends Service
{
    public function baseBySaved(Watch $model, bool $flag): array
    {
        return [
            'id' => $model->issue_id,
            'project_key' => $model->project_key,
            'user' => $model->user,
            'watching' => $flag,
        ];
    }

    public function base(Watch $model): array
    {
        return [
            'id' => $model->id,
            'issue_id' => $model->issue_id,
            'user' => $model->user,
        ];
    }

    public function formatList($models): array
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
