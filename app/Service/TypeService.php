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
use App\Model\ConfigType;
use App\Model\Project;
use App\Service\Dao\TypeDao;
use App\Service\Formatter\TypeFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class TypeService extends Service
{
    #[Inject]
    protected TypeDao $dao;

    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected TypeFormatter $formatter;

    public function findByProject(Project $project): array
    {
        $models = $this->dao->findByProjectKey($project->key);

        $list = [];
        /** @var ConfigType $model */
        foreach ($models as $model) {
            $item = $this->formatter->base($model);
            $item['is_used'] = di()->get(StateService::class)->isFieldUsedByIssue($project, 'type', ['id' => $model->id, 'project_key' => $project->key]);
            $list[] = $item;
        }

        $screens = $this->provider->getScreenList($project->key);
        $workflows = $this->provider->getWorkflowList($project->key);

        return [
            $list,
            [
                'screens' => $screens,
                'workflows' => $workflows,
            ],
        ];
    }

    public function save(int $id, string $key, array $attributes): ConfigType
    {
        return $this->dao->createOrUpdate($id, $key, $attributes);
    }

    public function delete(Project $project, int $id): int
    {
        $isUsed = di()->get(StateService::class)->isFieldUsedByIssue($project, 'type', ['id' => $id, 'project_key' => $project->key]);
        if ($isUsed) {
            throw new BusinessException(ErrorCode::TYPE_USED_ISSUE);
        }
        $model = $this->dao->delete($id);

        return $model->id;
    }

    public function sortable(string $key, array $sequence): array
    {
        return $this->dao->sortable($key, $sequence);
    }
}
