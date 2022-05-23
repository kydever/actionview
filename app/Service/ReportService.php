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
use App\Model\Report;
use App\Model\Sprint;
use App\Model\User;
use App\Model\Version;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\ReportDao;
use App\Service\Dao\SprintDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\VersionDao;
use App\Service\Dao\WorklogDao;
use App\Service\Formatter\IssueFormatter;
use App\Service\Formatter\ReportFormatter;
use App\Service\Formatter\WorklogFormatter;
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

    public function index(string $key, int $userId): array
    {
        $filters = ReportFiltersConstant::DEFAULT_REPORT_FILTERS;
        $reportFilters = $this->dao->getByProjectKey($key, $userId);
        $models = $this->formatter->formatList($reportFilters);
        foreach ($models as $model) {
            if (isset($model['filters'])) {
                $filters[$model['mode']][] = $model['filters'];
            }
        }
        foreach ($filters as $mode => $someFilters) {
            $filters[$mode] = $this->convFilters($key, $someFilters);
        }

        return $filters;
    }

    public function convFilters($project_key, array $filters)
    {
        foreach($filters as $key => $filter)
        {
            if ($filter['id'] === 'active_sprint')
            {
                $sprint = Sprint::where('project_key', $project_key)
                    ->where('status', 'active')
                    ->first();
                if ($sprint)
                {
                    $filters[$key]['query'] = [ 'sprints' => $sprint->no ];
                }
                else
                {
                    unset($filters[$key]);
                }
            }
            else if ($filter['id'] === 'latest_completed_sprint')
            {
                $sprint = Sprint::where('project_key', $project_key)
                    ->where('status', 'completed')
                    ->orderBy('no', 'desc')
                    ->first();
                if ($sprint)
                {
                    $filters[$key]['query'] = [ 'sprints' => $sprint->no ];
                }
                else
                {
                    unset($filters[$key]);
                }
            }
            else if ($filter['id'] === 'will_release_version')
            {
                $version = Version::where('project_key', $project_key)
                    ->where('status', 'unreleased')
                    ->orderBy('name', 'asc')
                    ->first();
                if ($version)
                {
                    $filters[$key]['query'] = [ 'resolve_version' => $version->id ];
                }
                else
                {
                    unset($filters[$key]);
                }
            }
            else if ($filter['id'] === 'latest_released_version')
            {
                $version = Version::where('project_key', $project_key)
                    ->where('status', 'released')
                    ->orderBy('name', 'desc')
                    ->first();
                if ($version)
                {
                    $filters[$key]['query'] = [ 'resolve_version' => $version->id ];
                }
                else
                {
                    unset($filters[$key]);
                }
            }
        }

        return array_values($filters);
    }

    public function saveFilter(string $mode, array $attributes, int $userId, string $projectKey): array
    {
        if (! in_array($mode, ReportFiltersConstant::MODE_MENU)) {
            throw new BusinessException(ErrorCode::FILTER_NAME_CANNOT_EMPTY);
        }
        $attributes = array_merge($attributes, compact('mode', 'userId', 'projectKey'));

        return $this->create($attributes);
    }

    public function create(array $attributes): array
    {
        $model = new Report();
        $model->user = $attributes['userId'];
        $model->project_key = $attributes['projectKey'];
        $model->mode = $attributes['mode'];
        $model->filters = [
            'id' => md5(microtime()),
            'name' => $attributes['name'],
            'query' => $attributes['query'],
        ];
        $model->save();

        return $this->formatter->base($model);
    }

    public function getIssues(string $x, ?string $y, User $user, Project $project, array $input): array
    {
        $data = [];
        $YAxis = [];
        $fields = $this->formatGroupByField([$x, $y]);
        if ($x === $y || is_null($y)) {
            $data[$x] = $this->initXYData($project->key, $x);
        } else {
            $data[$x] = $this->initXYData($project->key, $x);
            $data[$y] = $this->initXYData($project->key, $y);
        }

        $bool = di()->get(IssueService::class)->getBoolSearch($project->key, $input, $user->id);
        $res = di()->get(IssueSearch::class)->countByBoolQueryGroupBy($bool, $fields);

        $results = [];
        empty($data[$x]) && $data[$x] = $this->guessXYData($project->key, $x, $res);
        $y && empty($data[$y]) && $data[$y] = $this->guessXYData($project->key, $y, $res);

        if ($x === $y || ! $y) {
            foreach ($data[$x] as $key => $value) {
                $results[] = ['id' => $key, 'name' => $value['name'], 'cnt' => $res[$x][$key] ?? 0];
            }
        }
//            foreach ($data[$x] as $key => $value) {
//                $results[$key] = ['id' => $key, 'name' => $value['name'], 'y' => []];
//                $x_cnt = 0;
//
//                if ($YAxis) {
//                    foreach ($YAxis as $yai => $yav) {
//                        if (isset($data[$Y][$yai])) {
//                            $y_cnt = count(array_intersect($value['nos'], $data[$Y][$yai]['nos']));
//                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => $y_cnt];
//                            $x_cnt += $y_cnt;
//                        } else {
//                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => 0];
//                        }
//                    }
//                } else {
//                    foreach ($data[$y] as $key2 => $value2) {
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
        $user = get_user();
        $project = get_project();
        $interval = $attributes['interval'] ?? 'day';
        if (! in_array($interval, ['day', 'week', 'month'])) {
            throw new BusinessException(ErrorCode::FILTER_NAME_CANNOT_EMPTY);
        }

        $isAccu = $attributes['is_accu'] == 1 ? true : false;
        $project = di()->get(ProjectDao::class)->firstByKey($project->key, true);
        $startStatTime = strtotime((string) $project->created_at);
        $endStatTime = time();
        $where = di()->get(IssueService::class)->getBoolSearch($project->key, $attributes, $user->id);
        $statTime = $attributes['stat_time'] ?? null;
        if (! is_null($statTime)) {
            $or = [];
            $dateConds = [];
            $unitMap = ['d' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year'];
            $sections = explode('~', $statTime);
            if ($sections[0]) {
                $v = $sections[0];
                $unit = substr($v, -1);
                if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                    $direct = substr($v, 0, 1);
                    $vv = abs((int) substr($v, 0, -1));
                    $dateConds['$gte'] = strtotime(date('Ymd', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])));
                } else {
                    $dateConds['$gte'] = strtotime($v);
                }
                $startStatTime = max([$startStatTime, $dateConds['$gte']]);
            }

            if (isset($sections[1]) && $sections[1]) {
                $v = $sections[1];
                $unit = substr($v, -1);
                if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                    $direct = substr($v, 0, 1);
                    $vv = abs((int) substr($v, 0, -1));
                    $dateConds['$lte'] = strtotime(date('Y-m-d', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])) . ' 23:59:59');
                } else {
                    $dateConds['$lte'] = strtotime($v . ' 23:59:59');
                }
                $endStatTime = min([$endStatTime, $dateConds['$lte']]);
            }
        }

        $results = $this->getInitializedTrendData($interval, $startStatTime, $endStatTime);
        $issues = di()->get(IssueService::class)->getByProjectKey($project->key);
        $lists = di()->get(IssueFormatter::class)->formatList($issues);
        foreach ($lists as $list) {
            $createdAt = $list['created_at'] ?? null;
            if (! is_null($createdAt)) {
                $createdDate = $this->convDate($interval, $createdAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $createdDate) {
                            ++$results[$key]['new'];
                        }
                    }
                } elseif (isset($results[$createdDate]) && $list[$createdAt] >= $startStatTime) {
                    ++$results[$createdDate]['new'];
                }
            }

            $resolvedAt = $list['resolved_at'] ?? null;
            if (! is_null($resolvedAt)) {
                $resolvedDate = $this->convDate($interval, $resolvedAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $resolvedDate) {
                            ++$results[$key]['resolved'];
                        }
                    }
                } elseif (isset($results[$resolvedDate]) && $list['resolved_at'] >= $startStatTime) {
                    ++$results[$resolvedDate]['resolved'];
                }
            }

            $closedAt = $list['closed_at'] ?? null;
            if (! is_null($closedAt)) {
                $clsoedAt = $this->convDate($interval, $closedAt);
                if ($isAccu) {
                    foreach ($results as $key => $val) {
                        if ($key >= $closedAt) {
                            ++$results[$key]['closed'];
                        }
                    }
                } elseif (isset($results[$closedAt]) && $list['closed_at'] >= $startStatTime) {
                    ++$results[$closedAt]['closed'];
                }
            }
        }

        $data = array_values($results);
        $options = [
            'trend_start_stat_date' => date('Y/m/d', $startStatTime),
            'trend_end_stat_date' => date('Y/m/d', $endStatTime),
        ];

        return [$data, $options];
    }

    public function convDate(string $interval, int $at): string
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

    public function getInitializedTrendData(string $interval, int $startStatTime, int $endStatTime): array
    {
        $results = [];
        $t = $endStatTime;
        if ($interval = 'month') {
            $t = strtotime(date('Y/m/t', $endStatTime));
        } elseif ($interval == 'week') {
            $n = date('N', $endStatTime);
            $t = strtotime(date('Y/m/d', $endStatTime) . '+' . (7 - $n) . ' day');
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
            $y = (int) date('Y', $t);
            $m = (int) date('m', $t);
            $d = (int) date('d', $t);
            if ($interval == 'month') {
                $tmp['category'] = date('Y/m', $t);
                $t = mktime(0, 0, 0, $m - 1, $d, $y);
            } elseif ($interval == 'week') {
                $tmp['category'] = date('Y/m/d', $t - 6 * 24 * 3600);
                $t = mktime(0, 0, 0, $m, $d - 7, $y);
            } else {
                $tmp['category'] = date('Y/m/d', $t);
                $days[] = $tmp['category'];
                $weekFlg = (int) date('w', $t);
                $tmp['notWorking'] = ($weekFlg === 0 || $weekFlg === 6) ? 1 : 0;
                $t = mktime(0, 0, 0, $m, $d - 1, $y);
            }
            $results[$tmp['category']] = $tmp;
        }

        if ($days) {
            $singulars = di(CalendarSingularService::class)->getByDays($days);
            foreach ($singulars as $singular) {
                if (isset($results[$singular->key])) {
                    $results[$singular->day]['notWorking'] = $singular->type == 'holiday' ? 1 : 0;
                }
            }
        }

        return array_reverse($results);
    }

    public function getTimetracks(): array
    {
        $issues = di()->get(IssueService::class)->getByProjectKey(get_project_key());
        $list = [];
        foreach ($issues as $issue) {
            $item = di(IssueFormatter::class)->base($issue);
            $item['title'] = $issue->data['title'];
            $item['state'] = $issue->data['state'];
            $item['origin'] = $issue->data['original_estimate'] ?? '';
            $item['origin_m'] = $issue->data['original_estimatei_m'] ?? $this->ttHandleInM($item['origin']);

            $spendM = 0;
            $leftM = $item['origin_m'];
            $worklogs = di()->get(WorklogDao::class)->findManyProjectKeyAndIssueId(get_project_key(), $issue['id']);
            foreach ($worklogs as $worklog) {
                $log = di()->get(WorklogFormatter::class)->base($worklog);
                $thisSpendM = $log['spend_m'] ?? $this->ttHandleInM($log['spend'] ?? '');
                $spendM += $thisSpendM;
                if ($log['adjust_type'] == 1) {
                    $leftM = $leftM ? $leftM - $thisSpendM : '';
                } elseif ($log['adjust_type'] == 3) {
                    $leaveEstimate = $log['leave_estimate'] ?? '';
                    $leaveEstimateM = $log['leave_estimate_m'] ?? $this->ttHandleInM($leaveEstimate);
                    $leftM = $leaveEstimateM;
                } elseif ($log['adjust_type'] == 4) {
                    $cut = $log['cut'] ?? '';
                    $cutM = $log['cut_m'] ?? $this->ttHandleInM($cut);
                    $leftM = $leftM ? $leftM - $cutM : '';
                }
            }
            $item['spend_m'] = $spendM;
            $item['spend'] = $this->ttHandle($spendM . 'm');
            $item['left_m'] = $leftM ? max([$leftM, 0]) : '';
            $item['left'] = $leftM ?? $this->ttHandle(max([$leftM, 0]) . 'm');

            $list[] = $item;
        }

        return $list;
    }

    public function getTimetracksDetail(int $id): array
    {
        $worklogs = di()->get(WorklogDao::class)->findManyProjectKeyAndIssueId(get_project_key(), $id);
        $list = [];
        foreach ($worklogs as $worklog) {
            $item[] = di()->get(WorklogFormatter::class)->base($worklog);
            $spendM = $item['spend_m'] ?? null;
            if (is_null($spendM)) {
                $item['spend_m'] = $this->ttHandleInM($worklog->spend);
            }
            $list[] = $item;
        }

        return $list;
    }

    protected function guessXYData(string $key, string $field, $data): array
    {
        $result = [];
        switch ($field) {
            case 'assignee':
                $ids = array_keys($data[$field] ?? []);
                $models = di()->get(UserDao::class)->findMany($ids);
                foreach ($models as $model) {
                    $result[$model->id] = ['name' => $model->first_name];
                }
                break;
            case 'labels':
                foreach ($data[$field] ?? [] as $label => $count) {
                    $result[$label] = ['name' => $label];
                }
                break;
        }

        return $result;
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

    private function formatGroupByField($fields = [])
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = match ($field) {
                'assignee' => 'assignee.id',
                default => $field
            };
        }

        return array_unique($result);
    }
}
