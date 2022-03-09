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
use App\Service\Dao\TypeDao;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class TypeService extends Service
{
    #[Inject]
    protected TypeDao $dao;

    #[Inject]
    protected ProviderService $provider;

    public function getByProjectKeyOrderSnOldest(Project $project): array
    {
        $models = $this->dao->getByProjectKeyOrderSnOldest($project->key);
        $stateService = di()->get(StateService::class);
        foreach ($models as $model) {
            $model->is_used = $stateService->isFieldUsedByIssue($project, 'type', $model->toArray());
        }
        $screens = $this->provider->getScreenList($project->key);
        $workflows = $this->provider->getWorkflowList($project->key);
        $options = [
            'screens' => $screens,
            'workflows' => $workflows,
        ];

        return [
            'models' => $models,
            'options' => $options,
        ];
    }
}
