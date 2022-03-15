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

use App\Model\Report;
use Han\Utils\Service;

class ReportFormatter extends Service
{
    public function base(Report $model): array
    {
        return [
            'id' => $model->id,
            'mode' => $model->mode,
            'user' => $model->user,
            'filters' => $model->filters,
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
