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
namespace App\Service;

use App\Model\Project;
use App\Service\Dao\AccessProjectLogDao;
use Han\Utils\Service;

class ProjectService extends Service
{
    public function getLatestAccessProject(int $userId): ?Project
    {
        $model = di()->get(AccessProjectLogDao::class)->latest($userId);
        if ($model?->project?->isActive()) {
            return $model->project;
        }

        return null;
    }
}
