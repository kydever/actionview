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

use App\Constants\ReportFiltersConstant;
use App\Model\Project;
use App\Model\User;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\ReportDao;
use App\Service\Dao\SprintDao;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\ReportFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ReportService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected ReportDao $dao;

    #[Inject]
    protected ReportFormatter $formatter;

    #[Inject]
    protected ProviderService $provider;

    public function index(): array
    {
        $filters = ReportFiltersConstant::DEFAULT_REPORT_FILTERS;
        $models = $this->dao->getByProjectKey(get_project_key(), get_user_id());
        foreach ($models as $model) {
            if (isset($model['filters'])) {
                $filters[$model['mode']] = $model['filters'];
            }
        }
        foreach ($filters as $mode => $some_filters) {
            $filter[$mode] = $this->convFilters(get_project_key(), $some_filters);
        }

        return $filters;
    }

    public function convFilters(string $projectKey, array $filters)
    {
        foreach ($filters as $key => $filter) {
            if ($filter['id'] == 'active_sprint') {
                $sprint = di()->get(SprintDao::class)->firstByProjectKey($projectKey, 'active');
                if ($sprint) {
                    $filters[$key]['query'] = [
                        'sprints' => $sprint->no,
                    ];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'latest_completed_sprint') {
                $sprint = di()->get(SprintDao::class)->firstByProjectKey($projectKey, 'completed', 'desc');
                if ($sprint) {
                    $filters[$key]['query'] = [
                        'sprints' => $sprint->no,
                    ];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'will_release_version') {
                $version = di()->get(VersionDao::class)->firstByProjectKey(get_project_key(), 'unreleased');
                if ($version) {
                    $filters[$key]['query'] = ['resolve_version' => $version->id];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'latest_released_version') {
                $version = di()->get(VersionDao::class)->firstByProjectKey(get_project_key(), 'released');
                if ($version) {
                    $filters[$key]['query'] = ['resolve_version' => $version->id];
                } else {
                    unset($filters[$key]);
                }
            }
        }
        return array_values($filters);
    }

    public function getIssues(string $x, ?string $y, User $user, Project $project, array $input): array
    {
        $XYData = [];
        $YAxis = [];
        if ($x === $y || is_null($y)) {
            $XYData[$x] = $this->initXYData($project->key, $x);
        } else {
            $XYData[$x] = $this->initXYData($project->key, $x);
            $XYData[$y] = $this->initXYData($project->key, $y);
        }

        $bool = di()->get(IssueService::class)->getBoolSearch($project->key, $input, $user->id);
        $res = di()->get(IssueSearch::class)->countByBoolQueryGroupBy($bool, $x);

        $results = [];
        if ($x === $y || ! $y) {
            foreach ($XYData[$x] as $key => $value) {
                $results[] = ['id' => $key, 'name' => $value['name'], 'cnt' => $res[$key] ?? 0];
            }
        }
//            foreach ($XYData[$x] as $key => $value) {
//                $results[$key] = ['id' => $key, 'name' => $value['name'], 'y' => []];
//                $x_cnt = 0;
//
//                if ($YAxis) {
//                    foreach ($YAxis as $yai => $yav) {
//                        if (isset($XYData[$Y][$yai])) {
//                            $y_cnt = count(array_intersect($value['nos'], $XYData[$Y][$yai]['nos']));
//                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => $y_cnt];
//                            $x_cnt += $y_cnt;
//                        } else {
//                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => 0];
//                        }
//                    }
//                } else {
//                    foreach ($XYData[$y] as $key2 => $value2) {
//                        $y_cnt = count(array_intersect($value['nos'], $value2['nos']));
//
//                        $results[$key]['y'][] = ['id' => $key2, 'name' => $value2['name'], 'cnt' => $y_cnt];
//                        $x_cnt += $y_cnt;
//                    }
//                }
//                $results[$key]['cnt'] = $x_cnt;
//            }

        return array_values($results);
    }

    protected function initXYData(string $projectKey, string $dimension)
    {
        $results = [];
        switch ($dimension) {
            case 'type':
                $types = di()->get(ConfigTypeDao::class)->getTypeList($projectKey);
                foreach ($types as $type) {
                    $results[$type->id] = [
                        'name' => $type->name,
                        'nos' => [],
                    ];
                }
                break;
            case 'priority':
                $priorities = $this->provider->getPriorityOptions($projectKey);
                foreach ($priorities as $priority) {
                    $results[$priority['id']] = [
                        'name' => $priority['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'state':
                $states = di()->get(ProviderService::class)->getStateListOptions($projectKey);
                foreach ($states as $state) {
                    $results[$state['id']] = [
                        'name' => $state['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'resolution':
                $resolutions = $this->provider->getResolutionOptions($projectKey);
                foreach ($resolutions as $resolution) {
                    $results[$resolution['id']] = [
                        'name' => $resolution['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'module':
                $versions = di()->get(VersionService::class)->getByProjectKey($projectKey);
                foreach ($versions as $version) {
                    $results[$version->id] = [
                        'name' => $versions->name,
                        'nos' => [],
                    ];
                }
                $results = array_reverse($results);
                break;
            case 'epic':
                $epics = di()->get(EpicService::class)->getByProjectKey($projectKey);
                foreach ($epics as $epic) {
                    $results[$epic['id']] = [
                        'name' => $epic['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'sprints':
                $sprints = di()->get(SprintService::class)->getByProjectKeyAndStatus($projectKey, ['active', 'completed']);
                foreach ($sprints as $sprint) {
                    $results[$sprint->no] = [
                        'name' => 'Sprint' . $sprint->no,
                        'nos' => [],
                    ];
                }
                break;
            default:
                $filters = di()->get(FieldService::class)->getByProjectKey($projectKey);
                foreach ($filters as $filter) {
                    if ($filter->key === $dimension) {
                        if (isset($filter->optionValues) && $filter->optionValues) {
                            foreach ($filter->optionValues as $val) {
                                $results[$val['id']] = [
                                    'name' => $val['name'],
                                    'nos' => [],
                                ];
                            }
                        }
                        break;
                    }
                }
                break;
        }

        return $results;
    }
}
