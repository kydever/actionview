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

use App\Model\Project;
use Han\Utils\Service;

class ProjectFormatter extends Service
{
    public function small(Project $model): array
    {
        return [
            'key' => $model->key,
            'name' => $model->name,
            'principal' => $model->principal,
        ];
    }
}
