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

use App\Constants\IssueFiltersConstant;
use App\Constants\Permission;
use App\Constants\ProjectIssueListColumnConstant;
use App\Constants\Schema;
use App\Model\ConfigPriority;
use App\Model\ConfigPriorityProperty;
use App\Model\ConfigResolution;
use App\Model\ConfigResolutionProperty;
use App\Model\ConfigScreen;
use App\Model\ConfigState;
use App\Service\Context\GroupContext;
use App\Service\Dao\ConfigFieldDao;
use App\Service\Dao\ConfigPriorityDao;
use App\Service\Dao\ConfigPriorityPropertyDao;
use App\Service\Dao\ConfigResolutionDao;
use App\Service\Dao\ConfigResolutionPropertyDao;
use App\Service\Dao\ConfigStateDao;
use App\Service\Dao\ConfigStatePropertyDao;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\EpicDao;
use App\Service\Dao\IssueFilterDao;
use App\Service\Dao\LabelDao;
use App\Service\Dao\ModuleDao;
use App\Service\Dao\OswfDefinitionDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\ProjectIssueListColumnDao;
use App\Service\Dao\SprintDao;
use App\Service\Dao\SysSettingDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Dao\UserIssueFilterDao;
use App\Service\Dao\UserIssueListColumnDao;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\ConfigFieldFormatter;
use App\Service\Formatter\ConfigTypeFormatter;
use App\Service\Formatter\IssueFilterFormatter;
use App\Service\Formatter\LabelFormatter;
use App\Service\Formatter\SprintFormatter;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Database\Model\Collection;
use JetBrains\PhpStorm\ArrayShape;
use function Han\Utils\sort;

/**
 * TODO: 不懂为什么叫这个名字.
 */
class ProviderService extends Service
{
    #[Cacheable(prefix: 'user', group: 'context')]
    public function getUserList(string $key): array
    {
        $projects = di()->get(UserGroupProjectDao::class)->findByProjectKey($key);

        $userIds = [];
        $groupIds = [];
        foreach ($projects as $project) {
            $project->isGroup() ? $groupIds[] = $project->ug_id : $userIds[] = $project->ug_id;
        }

        if ($groupIds) {
            $groups = GroupContext::instance()->find($groupIds);
            foreach ($groups as $group) {
                $userIds = array_merge($userIds, $group->users);
            }
        }

        $userIds = array_values(array_unique($userIds));

        $models = di()->get(UserDao::class)->findMany($userIds);

        return di()->get(UserFormatter::class)->formatSmalls($models);
    }

    #[Cacheable(prefix: 'assigned:users', group: 'context')]
    public function getAssignedUsers(string $key)
    {
        $userIds = di()->get(AclService::class)->getUserIdsByPermission(Permission::ASSIGNED_ISSUE, $key);

        $models = di()->get(UserDao::class)->findMany($userIds);

        return di()->get(UserFormatter::class)->formatSmalls($models);
    }

    #[Cacheable(prefix: 'workflow:by:type', group: 'context')]
    public function getWorkflowByType(int $type)
    {
        $model = di()->get(ConfigTypeDao::class)->first($type, true);

        return $model->workflow;
    }

    public function getStateListOptions(string $key): array
    {
        $states = $this->getStateList($key);
        $result = [];
        foreach ($states as $state) {
            $result[] = [
                'id' => $state->key ?: $state->id,
                'name' => trim($state->name),
                'category' => $state->category,
            ];
        }

        return $result;
    }

    /**
     * @return Collection<int, ConfigState>
     */
    public function getStateList(string $key): Collection
    {
        $states = di()->get(ConfigStateDao::class)->findOrByProjectKey($key);

        $property = di()->get(ConfigStatePropertyDao::class)->firstByProjectKey($key);
        if ($sequence = $property?->sequence) {
            $sequence = array_flip($sequence);
            $result = sort($states, static function (ConfigState $model) use ($sequence) {
                return -($sequence[$model->id] ?? 999);
            })->toArray();

            $states = new Collection($result);
        }

        return $states;
    }

    #[ArrayShape([Collection::class, ConfigResolutionProperty::class])]
    public function getResolutionList(string $key): array
    {
        $resolutions = di()->get(ConfigResolutionDao::class)->findOrByProjectKey($key);
        $property = di()->get(ConfigResolutionPropertyDao::class)->firstByProjectKey($key);
        if ($sequence = $property?->sequence) {
            $sequence = array_flip($sequence);
            $result = sort($resolutions, static function (ConfigResolution $model) use ($sequence) {
                return -($sequence[$model->id] ?? 999);
            })->toArray();

            $resolutions = new Collection($result);
        }

        return [$resolutions, $property];
    }

    #[ArrayShape([Collection::class, ConfigPriorityProperty::class])]
    public function getPriorityList(string $key)
    {
        $priorities = di()->get(ConfigPriorityDao::class)->findOrByProjectKey($key);

        $property = di()->get(ConfigPriorityPropertyDao::class)->firstByProjectKey($key);

        if ($sequence = $property?->sequence) {
            $sequence = array_flip($sequence);
            $result = sort($priorities, static function (ConfigPriority $model) use ($sequence) {
                return -($sequence[$model->id] ?? 999);
            })->toArray();

            $priorities = new Collection($result);
        }

        return [$priorities, $property];
    }

    #[Cacheable(prefix: 'priority', group: 'context')]
    public function getPriorityOptions(string $key)
    {
        [$priorities, $property] = $this->getPriorityList($key);

        $options = [];
        /** @var ConfigPriority $priority */
        foreach ($priorities as $priority) {
            $item = [
                'id' => $priority->key ?: $priority->id,
                'name' => trim($priority->name ?? ''),
                'color' => $priority->color,
            ];

            if ($property?->default_value == $priority->id) {
                $item['default'] = true;
            }

            $options[] = $item;
        }
        return $options;
    }

    #[Cacheable(prefix: 'resolution', group: 'context')]
    public function getResolutionOptions(string $key)
    {
        [$resolutions, $property] = $this->getResolutionList($key);
        $options = [];
        /** @var ConfigResolution $resolution */
        foreach ($resolutions as $resolution) {
            $item = [
                'id' => $resolution->key ?: $resolution->id,
                'name' => trim($resolution->name ?? ''),
            ];

            if ($property?->default_value == $resolution->id) {
                $item['default'] = true;
            }

            $options[] = $item;
        }
        return $options;
    }

    #[Cacheable(prefix: 'module', group: 'context')]
    public function getModuleList(string $key): array
    {
        $models = di(ModuleDao::class)->getModuleList($key);

        return $models->columns(['id', 'name'])->toArray();
    }

    #[Cacheable(prefix: 'epic', group: 'context')]
    public function getEpicList(string $key): array
    {
        $models = di(EpicDao::class)->getEpicList($key);

        return $models->columns(['id', 'name', 'bgColor'])->toArray();
    }

    #[Cacheable(prefix: 'version', group: 'context')]
    public function getVersionList(string $key)
    {
        $versions = di()->get(VersionDao::class)->findByProjectKey($key);
        $result = [];
        foreach ($versions as $version) {
            $result[] = [
                'id' => (string) $version->id,
                'name' => $version->name,
            ];
        }
        return $result;
    }

    #[Cacheable(prefix: 'label', group: 'context')]
    public function getLabelOptions(string $key): array
    {
        $models = di(LabelDao::class)->getLabelOptions($key);
        return di(LabelFormatter::class)->formatList($models);
    }

    public function getTypeListExt(string $key): array
    {
        $models = di(ConfigTypeDao::class)->getTypeList($key, ['screen']);

        $typeOptions = [];
        foreach ($models as $model) {
            $schema = $this->getScreenSchema($key, $model->id, $model->screen);
            $type = di(ConfigTypeFormatter::class)->small($model);
            $type['schema'] = $schema;
            $typeOptions[] = $type;
        }
        return $typeOptions;
    }

    public function getSprintList(string $key, bool $isNew = true): array
    {
        $models = di(SprintDao::class)->getSprintList($key);
        $result = [];
        foreach ($models as $model) {
            $sprint = di(SprintFormatter::class)->base($model);
            if (empty($sprint['name'])) {
                $sprint['name'] = 'Sprint ' . $sprint['no'];
            }
            if ($isNew) {
                $result[] = [
                    'no' => $sprint['no'],
                    'name' => $sprint['name'],
                ];
            } else {
                $result[] = $sprint;
            }
        }
        return $result;
    }

    #[Cacheable(prefix: 'field', group: 'context')]
    public function getFieldList(string $key)
    {
        return di()->get(ConfigFieldDao::class)->getFieldList($key);
    }

    public function getFieldListOptions(string $key): array
    {
        $models = $this->getFieldList($key);

        return di(ConfigFieldFormatter::class)->formatList($models);
    }

    public function getIssueFilters(string $key, int $userId): array
    {
        $filters = IssueFiltersConstant::DEFAULT_ISSUE_FILTERS;

        $customizeFilterModels = di(IssueFilterDao::class)->getIssueFilters($key, $userId);
        $customizeFilters = di(IssueFilterFormatter::class)->formatList($customizeFilterModels);
        $filters = array_merge($filters, $customizeFilters);
        $userFilter = di(UserIssueFilterDao::class)->getUserFilter($key, $userId);
        if ($sequence = $userFilter?->sequence ?? []) {
            $sequence = array_flip($sequence);
            return sort($filters, static function ($model) use ($sequence) {
                return -($sequence[$model['id']] ?? 999);
            })->toArray();
        }

        return $filters;
    }

    public function getIssueDisplayColumns(string $key, int $userId): array
    {
        $userIssueListColumnModel = di(UserIssueListColumnDao::class)->getUserDisplayColumns($key, $userId);
        if ($columns = $userIssueListColumnModel?->columns) {
            return $columns;
        }

        $projectIssueListColumnModel = di(ProjectIssueListColumnDao::class)->getDisplayColumns($key);
        if ($columns = $projectIssueListColumnModel?->columns) {
            return $columns;
        }
        return ProjectIssueListColumnConstant::DEFAULT_DISPLAY_COLUMNS;
    }

    public function getTimeTrackSetting(): array
    {
        $options = ['w2d' => 5, 'd2h' => 8];
        $model = di(SysSettingDao::class)->first();
        if ($properties = $model?->properties) {
            $options['w2d'] = $properties['week2day'] ?? $options['w2d'];
            $options['d2h'] = $properties['day2hour'] ?? $options['d2h'];
        }
        return $options;
    }

    public function getLinkRelations(): array
    {
        return [
            ['id' => 'blocks', 'out' => 'blocks', 'in' => 'is blocked by'],
            ['id' => 'clones', 'out' => 'clones', 'in' => 'is cloned by'],
            ['id' => 'duplicates', 'out' => 'duplicates', 'in' => 'is duplicated by'],
            ['id' => 'relates', 'out' => 'relates to', 'in' => 'relates to'],
        ];
    }

    public function getSchemaByType(int $typeId): array
    {
        $type = di()->get(ConfigTypeDao::class)->first($typeId, false);
        if (! $type) {
            return [];
        }

        return $this->getScreenSchema($type->project_key, $typeId, $type->screen);
    }

    public function getScreenSchema(string $projectKey, int $typeId, ConfigScreen $screen): array
    {
        $newSchema = [];
        $versions = null;
        $users = null;
        foreach ($screen->schema ?: [] as $key => $val) {
            if (isset($val['applyToTypes'])) {
                if (! in_array($typeId, explode(',', (string) $val['applyToTypes']))) {
                    continue;
                }
                unset($val['applyToTypes']);
            }

            if ($val['key'] == Schema::ASSIGNEE) {
                $users = $this->getAssignedUsers($projectKey);
                foreach ($users as $key => $user) {
                    $users[$key]['name'] = $user['name'] . '(' . $user['email'] . ')';
                }
                $val['optionValues'] = $users;
            } elseif ($val['key'] == Schema::RESOLUTION) {
                $resolutions = $this->getResolutionOptions($projectKey);
                $val['optionValues'] = $resolutions;
                foreach ($resolutions as $v) {
                    if ($v['default'] ?? null) {
                        $val['defaultValue'] = $v['id'];
                        break;
                    }
                }
            } elseif ($val['key'] == Schema::PRIORITY) {
                $priorities = $this->getPriorityOptions($projectKey);
                $val['optionValues'] = $priorities;
                foreach ($priorities as $v) {
                    if ($v['default'] ?? null) {
                        $val['defaultValue'] = $v['id'];
                        break;
                    }
                }
            } elseif ($val['key'] == Schema::MODULE) {
                $modules = $this->getModuleList($projectKey);
                $val['optionValues'] = $modules;
            } elseif ($val['key'] == Schema::EPIC) {
                $epics = $this->getEpicList($projectKey);
                $val['optionValues'] = $epics;
            } elseif ($val['key'] == Schema::LABELS) {
                $labels = $this->getLabelOptions($projectKey);
                $coupleLabels = [];
                foreach ($labels as $label) {
                    $coupleLabels[] = ['id' => $label['name'], 'name' => $label['name']];
                }
                $val['optionValues'] = $coupleLabels;
            } elseif ($val['type'] == 'SingleVersion' || $val['type'] == 'MultiVersion') {
                $versions === null && $versions = $this->getVersionList($projectKey);
                $val['optionValues'] = $versions;
            } elseif ($val['type'] == 'SingleUser' || $val['type'] == 'MultiUser') {
                $users === null && $users = $this->getUserList($projectKey);
                foreach ($users as $key => $user) {
                    $users[$key]['name'] = $user['name'] . '(' . $user['email'] . ')';
                }
                $val['optionValues'] = $users;
            }

            $newSchema[] = $val;
        }

        return $newSchema;
    }

    public function getModuleById(int $id): array
    {
        $model = di()->get(ModuleDao::class)->first($id);

        return $model?->toArray() ?? [];
    }

    public static function getProjectPrincipal(string $key)
    {
        $project = di()->get(ProjectDao::class)->firstByKey($key);

        return $project?->getPrincipal();
    }

    public function getWorkflowList(string $projectKey, array $fields = [])
    {
        return di()->get(OswfDefinitionDao::class)->getByFieldsList($projectKey, $fields);
    }
}
