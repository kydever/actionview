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

use App\Model\ConfigState;
use App\Model\Project;
use App\Model\User;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigStateDao;
use App\Service\Formatter\StateFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class StateService extends Service
{
    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected ConfigStateDao $dao;

    #[Inject]
    protected StateFormatter $formatter;

    public function index(Project $project, User $user)
    {
        $states = $this->provider->getStateList($project->key);
        $result = [];
        foreach ($states as $state) {
            $item = $this->formatter->base($state);
            $item['is_used'] = $this->isFieldUsedByIssue($project, 'state', [
                'id' => $state->id,
                'name' => $state->name,
                'project_key' => $state->project_key,
            ]);

            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param $field = [
     *     'id' => 1,
     *     'name' => '',
     *     'project_key' => '',
     * ]
     */
    public function isFieldUsedByIssue(Project $project, string $fieldKey, array $field, array $extra = [])
    {
        if ($field['project_key'] !== $project->key) {
            return true;
        }

        if ($project->isSYS()) {
            return true;
        }

        switch ($fieldKey) {
            case 'type':
            case 'state':
            case 'priority':
            case 'resolution':
            case 'epic':
            case 'module':
                return di()->get(IssueSearch::class)->countWhereTerm($project->key, $fieldKey, $field['id']) > 0;
            case 'labels':
                return di()->get(IssueSearch::class)->countWhereTerm($project->key, $fieldKey, $field['name']) > 0;
            case 'version':
                if (! $extra) {
                    return false;
                }

                // $vid = $field['id'];
                // return DB::collection('issue_' . $project_key)
                //     ->where(function ($query) use ($vid, $extra) {
                //         foreach ($extra as $key => $vf) {
                //             $query->orWhere($vf['key'], $vid);
                //         }
                //     })
                //     ->where('del_flg', '<>', 1)
                //     ->exists();
                return true;
            default:
                return true;
        }
    }

    public function save(int $id, string $projectKey, array $attributes): ConfigState
    {
        return $this->dao->createOrUpdate($id, $projectKey, $attributes);
    }

    public function delete(Project $project, int $id): bool
    {
        return $this->dao->delete($project, $id);
    }
}
