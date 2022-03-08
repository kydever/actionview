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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\OswfDefinition;
use App\Model\Project;
use App\Model\User;
use App\Service\Dao\OswfDefinitionDao;
use App\Service\Formatter\DefinitionFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class WorkflowService extends Service
{
    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected OswfDefinitionDao $dao;

    #[Inject]
    protected DefinitionFormatter $formatter;

    public function info(int $id, Project $project): array
    {
        $workflow = $this->dao->first($id);
        if ($project->key !== $workflow?->project_key) {
            throw new BusinessException(ErrorCode::WORKFLOW_NOT_EXISTS);
        }

        $states = $this->provider->getStateListOptions($project->key);
        $screens = $this->provider->getScreenList($project->key);
        $resolutions = $this->provider->getResolutionOptions($project->key);
        $roles = $this->provider->getRoleList($project->key);
        $events = $this->provider->getEventOptions($project->key);
        $users = $this->provider->getUserList($project->key);

        return [
            $this->formatter->base($workflow),
            [
                'states' => $states,
                'screens' => $screens,
                'resolutions' => $resolutions,
                'events' => $events,
                'roles' => $roles,
                'users' => $users,
            ],
        ];
    }

    public function preview(int $id)
    {
        $model = $this->dao->first($id, true);

        return $this->formatter->base($model);
    }

    public function save(int $id, User $user, string $projectKey, array $attributes): OswfDefinition
    {
        return $this->dao->createOrUpdate($id, $user, $projectKey, $attributes);
    }
}
