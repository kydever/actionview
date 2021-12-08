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
namespace App\Service\Context;

use App\Service\Dao\ProjectDao;
use Han\Utils\ContextInstance;

class ProjectContext extends ContextInstance
{
    protected ?string $key = 'key';

    protected function initModels(array $ids): array
    {
        return di()->get(ProjectDao::class)->findByKeys($ids)->getDictionary();
    }
}
