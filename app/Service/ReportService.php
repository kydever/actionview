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
use App\Constants\ReportFiltersConstant;
use App\Exception\BusinessException;
use App\Model\Project;
use App\Model\User;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\ProjectDao;
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

    public function getTrends(array $attributes): array
    {
        $interval = $attributes['interval'] ?? 'day';
        if (! in_array($interval, ['day', 'week', 'month'])) {
            throw new BusinessException(ErrorCode::FILTER_NAME_CANNOT_EMPTY);
        }
        $isAccu = $attributes['is_accu'] == 1 ? true : false;
        $project = di()->get(ProjectDao::class)->firstByKey(get_project_key(), true);
        $startStatTime = strtotime((string) $project->created_at);
        $endStatTime = time();
        $where = di()->get(IssueService::class)->getBoolSearch($project->key, $attributes, get_user_id());
        $statTime = $attributes['stat_time'] ?? null;
        if (! is_null($statTime)) {
            $or = [];
            $dateConds = [];
            $unitMap = [
                'd' => 'day',
                'w' => 'week',
                'm' => 'month',
                'y' => 'year',
            ];
            $sections = explode('~', $statTime);
            $section = $sections[0] ?? null;
            if (! is_null($section)) {
                $unit = substr($section, -1);
                if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                    $direct = substr($section, 0, 1);
                    $vv = abs((int) substr($section, 0, -1));
                    $dateConds['$gte'] = strtotime(date('Ymd', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])));
                } else {
                    $dateConds['$gte'] = strtotime($section);
                }
                $startStatTime = max([$startStatTime, $dateConds['$gte']]);
            }

            $sections1 = $sections[1] ?? null;
            if (! is_null($sections1)) {
                $unit = substr($section, -1);
                if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                    $direct = substr($sections1, 0, 1);
                    $vv = abs((int) substr($sections1, 0, -1));
                    $dateConds['$lte'] = strtotime(date('Y-m-d', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])) . ' 23:59:59');
                } else {
                    $dateConds['$lte'] = strtotime($sections1 . ' 23:59:59');
                }
                $endStatTime = min([$endStatTime, $dateConds['$lte']]);
            }

            if ($dateConds) {
                $or[] = ['created_at' => $dateConds];
                $or[] = ['resolved_at' => $dateConds];
                $or[] = ['closed_at' => $dateConds];
            }

            if (! $isAccu && $or) {
                $where['$and'][] = ['$or' => $or];
            } else {
                $where['$and'][] = [
                    'created_at' => [
                        '$lte' => $endStatTime,
                    ],
                ];
            }
        }

        $results = $this->getInitializedTrendData($interval, $startStatTime, $endStatTime);

        $issues = di()->get(IssueService::class)->getByProjectKey($project->key, $where, ['created_at', 'resolved_at', 'closed_at']);
        foreach ($issues as $issue) {
            $createdAt = $issue['created_at'] ?? null;
            if (! is_null($createdAt)) {
                $createdDate = $this->convDate($interval, $createdAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $createdDate) {
                            ++$results[$key]['new'];
                        }
                    }
                } elseif (isset($results[$createdDate]) && $issue['created_at'] >= $startStatTime) {
                    ++$results[$createdDate]['new'];
                }
            }
            $resolvedAt = $issue['resolved_at'];
            if (! is_null($resolvedAt)) {
                $resolvedDate = $this->convDate($interval, $resolvedAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $resolvedDate) {
                            ++$results[$key]['resolved'];
                        }
                    }
                } elseif (isset($results[$resolvedDate]) && $issue['resolved_at'] >= $startStatTime) {
                    ++$results[$resolvedDate]['resolved'];
                }
            }
            $closedAt = $issue['closed_at'] ?? null;
            if (! is_null($closedAt)) {
                $closedDate = $this->convDate($interval, $closedAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $resolvedDate) {
                            ++$results[$key]['closed'];
                        }
                    }
                } elseif (isset($results[$closedDate]) && $issue['closed_at'] >= $startStatTime) {
                    ++$results[$closedDate]['closed'];
                }
            }
        }

        $data = array_values($results);
        $options = [
            'trend_start_stat_date' => date('Y/m/d', $startStatTime),
            'trend_end_stat_date' => date('Y/m/d', $startStatTime),
        ];

        return [$data, $options];
    }

    public function convDate(string $interval, string $at)
    {
        if ($interval == 'week') {
            $n = date('N', $at);

            return date('Y/m/d', $at - ($n - 1) * 24 * 3600);
        }
        if ($interval == 'month') {
            return date('Y/m', $at);
        }
        return date('Y/m/d', $at);
    }

    public function getInitializedTrendData($interval, $startStatTime, $endStatTime): array
    {
        $results = [];
        $t = $endStatTime;
        if ($interval == 'month') {
            $t = strtotime(date('Y/m/t', $endStatTime));
        } elseif ($interval == 'week') {
            $n = date('N', $endStatTime);
            $t = strtotime(date('Y/m/d', $endStatTime) . ' +' . (7 - $n) . ' day');
        } else {
            $t = strtotime(date('Y/m/d', $endStatTime));
        }
        $i = 0;
        $days = [];
        for (; $t >= $startStatTime && $i < 100; ++$i) {
            $tmp = [
                'new' => 0,
                'resolved' => 0,
                'closed' => 0,
            ];
            $y = date('Y', $t);
            $m = date('m', $t);
            $d = date('d', $t);
            if ($interval == 'month') {
                $tmp['category'] = date('Y/m', $t);
                $t = mktime(0, 0, 0, $m - 1, (int) $d, (int) $y);
            } elseif ($interval == 'week') {
                $tmp['category'] = date('Y/m/d', $t - 6 * 24 * 3600);
                $t = mktime(0, 0, 0, (int) $m, $d - 7, (int) $y);
            } else {
                $tmp['category'] = date('Y/m/d', $t);
                $days[] = $tmp['category'];
                $week_flg = intval('w', $t);
                $tmp['notWorking'] = ($week_flg === 0 || $week_flg === 6) ? 1 : 0;
                $t = mktime(0, 0, 0, (int) $m, $d - 1, (int) $y);
            }
            $results[$tmp['category']] = $tmp;
        }

        if ($days) {
            $singulars = di(CalendarSingularService::class)->getByDays($days);
            foreach ($singulars as $singular) {
                if (isset($results[$singular->day])) {
                    $results[$singular->day]['notWorking'] = ($singular->type == 'holiday' ? 1 : 0);
                }
            }
        }

        return array_reverse($results);
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
