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

use App\Model\IssueFilter;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class IssueFilterFormatter extends Service
{
    public function base(IssueFilter $model): array
    {
        return [
            'id' => (string) $model->id,
            'name' => $model->name,
            'project_key' => $model->project_key,
            'query' => $model->query,
            'creator' => $model->creator,
            'scope' => $model->scope,
        ];
    }

    public function formatList(Collection $models)
    {
        $result = [];
        /** @var IssueFilter[] $models */
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }
        return $result;
    }
}
