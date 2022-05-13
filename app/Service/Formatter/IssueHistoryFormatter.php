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

use App\Model\IssueHistory;
use Han\Utils\Service;

class IssueHistoryFormatter extends Service
{
    public function base(IssueHistory $model): array
    {
        return [
            'operation' => $model->operation,
            'operated_at' => $model->operated_at,
            'operator' => $model->operator,
            'data' => $model->data,
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
