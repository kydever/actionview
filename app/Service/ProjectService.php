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
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Formatter\ProjectFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ProjectService extends Service
{
    #[Inject]
    protected ProjectDao $dao;

    #[Inject]
    protected AclService $acl;

    #[Inject]
    protected ProjectFormatter $formatter;

    public function getLatestAccessProject(int $userId): ?Project
    {
        $model = di()->get(AccessProjectLogDao::class)->latest($userId);
        if ($model?->project?->isActive()) {
            return $model->project;
        }

        return null;
    }

    public function recent(int $userId)
    {
        $groupIds = di()->get(AclGroupDao::class)->findByUserId($userId)->columns('id')->toArray();
        $projectKeys = di()->get(UserGroupProjectDao::class)->findByUGIds([$userId, ...$groupIds])->columns('project_key')->toArray();
        $accessedProjectKeys = di()->get(AccessProjectLogDao::class)->findLatestProjectKeys($userId);

        $keys = array_values(array_unique(array_intersect($projectKeys, $accessedProjectKeys)));

        $projects = di()->get(ProjectDao::class)->findByKeys($keys);
        $result = [];
        foreach ($projects as $project) {
            if (! $project->isActive()) {
                continue;
            }

            $result[] = $this->formatter->small($project);

            if (count($result) >= 5) {
                break;
            }
        }

        return $result;
    }
}
