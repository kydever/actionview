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
use App\Service\Dao\ReportDao;
use App\Service\Dao\SprintDao;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\ConfigFieldFormatter;
use App\Service\Formatter\ReportFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;

class ReportService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected ReportDao $dao;

    #[Inject]
    protected ReportFormatter $formatter;

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

    public function getIssues(string $X, ?string $Y): array
    {
        $project = get_project();
        $XYData = [];
        $YAxis = [];
        if ($X === $Y || is_null($Y)) {
            $XYData[$X] = $this->initXYData($project->key, $X);
        } else {
            $XYData[$X] = $this->initXYData($project->key, $X);
            $XYData[$Y] = $this->initXYData($project->key, $Y);
        }
        $where = $this->getIssueQueryWhere($project->key, [$X, $Y]);
        $issues = di()->get(IssueService::class)->getByProjectKeyWhereRaw($project->key, $where);
        foreach ($issues as $issue) {
            foreach ($XYData as $dimension => $z) {
                if (! isset($issue[$dimension]) || ! $issue[$dimension]) {
                    continue;
                }

                $issue_vals = [];
                if (is_string($issue[$dimension])) {
                    if (strpos($issue[$dimension], ',') !== false) {
                        $issue_vals = explode(',', $issue[$dimension]);
                    } else {
                        $issue_vals = [$issue[$dimension]];
                    }
                } elseif (is_array($issue[$dimension])) {
                    $issue_vals = $issue[$dimension];
                    if (isset($issue[$dimension]['id'])) {
                        $issue_vals = [$issue[$dimension]];
                    }
                }

                foreach ($issue_vals as $issue_val) {
                    $tmpv = $issue_val;
                    if (is_array($issue_val) && isset($issue_val['id'])) {
                        $tmpv = $issue_val['id'];
                    }

                    if (isset($z[$tmpv])) {
                        $XYData[$dimension][$tmpv]['nos'][] = $issue['no'];
                    } elseif ((is_array($issue_val) && isset($issue_val['id'])) || $dimension === 'labels') {
                        if ($dimension === $Y && $X !== $Y) {
                            $YAxis[$tmpv] = isset($issue[$dimension]['name']) ? $issue[$dimension]['name'] : $tmpv;
                        }

                        $XYData[$dimension][$tmpv] = ['name' => isset($issue[$dimension]['name']) ? $issue[$dimension]['name'] : $tmpv, 'nos' => [$issue['no']]];
                    }
                }
            }
        }

        $results = [];
        if ($X === $Y || ! $Y) {
            foreach ($XYData[$X] as $key => $value) {
                $results[] = ['id' => $key, 'name' => $value['name'], 'cnt' => count($value['nos'])];
            }
        } else {
            foreach ($XYData[$X] as $key => $value) {
                $results[$key] = ['id' => $key, 'name' => $value['name'], 'y' => []];
                $x_cnt = 0;

                if ($YAxis) {
                    foreach ($YAxis as $yai => $yav) {
                        if (isset($XYData[$Y][$yai])) {
                            $y_cnt = count(array_intersect($value['nos'], $XYData[$Y][$yai]['nos']));
                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => $y_cnt];
                            $x_cnt += $y_cnt;
                        } else {
                            $results[$key]['y'][] = ['id' => $yai, 'name' => $yav, 'cnt' => 0];
                        }
                    }
                } else {
                    foreach ($XYData[$Y] as $key2 => $value2) {
                        $y_cnt = count(array_intersect($value['nos'], $value2['nos']));

                        $results[$key]['y'][] = ['id' => $key2, 'name' => $value2['name'], 'cnt' => $y_cnt];
                        $x_cnt += $y_cnt;
                    }
                }
                $results[$key]['cnt'] = $x_cnt;
            }
        }

        return array_values($results);
    }

    protected function initXYData(string $projectKey, string $dimension)
    {
        $results = [];
        switch ($dimension) {
            case 'type':
                $types = di()->get(TypeService::class)->getTypeList($projectKey, ['name']);
                foreach ($types as $type) {
                    $results[$type->id] = [
                        'name' => $type->name,
                        'nos' => [],
                    ];
                }
                break;
            case 'priority':
                $priorities = di()->get(PriorityService::class)->getPriorityOptions($projectKey);
                foreach ($priorities as $priority) {
                    $results[$priority['id']] = [
                        'name' => $priority['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'state':
                $states = di()->get(StateService::class)->getStateOptions($projectKey);
                foreach ($states as $state) {
                    $results[$state['id']] = [
                        'name' => $state['name'],
                        'nos' => [],
                    ];
                }
                break;
            case 'resolution':
                $resolutions = di()->get(ResolutionService::class)->getResolutionOptions($projectKey);
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
                        if (isset($field->optionValues) && $field->optionValues) {
                            foreach ($field->optionValues as $val) {
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

    protected function getIssueQueryWhere(string $projectKey, array $query)
    {
        $special_fields = [
            ['key' => 'no', 'type' => 'Number'],
            ['key' => 'type', 'type' => 'Select'],
            ['key' => 'state', 'type' => 'Select'],
            ['key' => 'assignee', 'type' => 'SingleUser'],
            ['key' => 'reporter', 'type' => 'SingleUser'],
            ['key' => 'resolver', 'type' => 'SingleUser'],
            ['key' => 'closer', 'type' => 'SingleUser'],

            ['key' => 'created_at', 'type' => 'Duration'],
            ['key' => 'updated_at', 'type' => 'Duration'],
            ['key' => 'resolved_at', 'type' => 'Duration'],
            ['key' => 'closed_at', 'type' => 'Duration'],

            ['key' => 'sprints', 'type' => 'Select'],
        ];

        $filters = di()->get(FieldService::class)->getByProjectKey($projectKey, ['key', 'name', 'type']);
        $lists = [];
        foreach ($filters as $filter) {
            $lists[] = di()->get(ConfigFieldFormatter::class)->base($filter);
        }
        $all_filters = array_merge($lists, $special_fields);
        $key_type_fields = [];
        foreach ($all_filters as $filter) {
            $key_type_fields[$filter['key']] = $filter['type'];
        }
        $where = Arr::only($query, array_column($all_filters, 'key'));
        $and = [];
        foreach ($where as $key => $val) {
            if ($key == 'no') {
                $and[] = [
                    'no' => intval($val),
                ];
            } elseif ($key == 'title') {
                if (is_numeric($val) && strpos($val, '.') === false) {
                    $and[] = [
                        '$or' => [
                            [
                                'no' => $val + 0,
                            ],
                            [
                                'title' => [
                                    '$regex' => $val,
                                ],
                            ],
                        ],
                    ];
                } elseif (strpos($val, ',') !== false) {
                    $nos = explode(',', $val);
                    $new_nos = [];
                    foreach ($nos as $no) {
                        if ($no && is_numeric($no)) {
                            $new_nos[] = $no + 0;
                        }
                    }
                    $and[] = [
                        '$or' => [
                            [
                                'no' => [
                                    '$in' => $new_nos,
                                ],
                            ],
                        ],
                    ];
                } else {
                    $and[] = [
                        'title' => [
                            '$regex' => $val,
                        ],
                    ];
                }
            } elseif ($key == 'sprints') {
                $and[] = [
                    'sprints' => $val + 0,
                ];
            } elseif ($key_type_fields[$key] == 'SingleUser') {
                $users = explode(',', $val);
                if (in_array('me', $users)) {
                    array_push($users, get_user_id());
                }
                $and[] = [
                    $key . '.' . 'id' => ['$in' => $users],
                ];
            } elseif ($key_type_fields[$key] == 'MultiUser') {
                $or = [];
                $vals = explode(',', $val);
                foreach ($vals as $v) {
                    $or[] = [$key . '_ids' => $v == 'me' ? get_user_id() : $v];
                }
                $and[] = ['$or' => $or];
            } elseif (in_array($key_type_fields[$key], ['Select', 'SingleVersion', 'RadioGroup'])) {
                $and[] = [
                    $key => [
                        '$in' => explode(',', $val),
                    ],
                ];
            } elseif (in_array($key_type_fields[$key], ['MultiSelect', 'MultiVersion', 'CheckboxGroup'])) {
                $or = [];
                $vals = explode(',', $val);
                foreach ($vals as $v) {
                    $or[] = [$key => $v];
                }
                $and[] = ['$or' => $or];
            } elseif (in_array($key_type_fields[$key], ['Duration', 'DatePicker', 'DateTimePicker'])) {
                if (in_array($val, ['0d', '0w', '0m', '0y'])) {
                    if ($val == '0d') {
                        $and[] = [
                            $key => [
                                '$gte' => strtotime(date('Y-m-d')), '$lte' => strtotime(date('Y-m-d') . ' 23:59:59'),
                            ],
                        ];
                    } elseif ($val == '0w') {
                        $and[] = [
                            $key => [
                                '$gte' => mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y')), '$lte' => mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y')),
                            ],
                        ];
                    } elseif ($val == '0m') {
                        $and[] = [
                            $key => [
                                '$gte' => mktime(0, 0, 0, date('m'), 1, date('Y')), '$lte' => mktime(23, 59, 59, date('m'), date('t'), date('Y')),
                            ],
                        ];
                    } else {
                        $and[] = [
                            $key => [
                                '$gte' => mktime(0, 0, 0, 1, 1, date('Y')), '$lte' => mktime(23, 59, 59, 12, 31, date('Y')),
                            ],
                        ];
                    }
                } else {
                    $date_conds = [];
                    $unitMap = ['d' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year'];
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $v = $sections[0];
                        $unit = substr($v, -1);
                        if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                            $direct = substr($v, 0, 1);
                            $vv = abs(substr($v, 0, -1));
                            $date_conds['$gte'] = strtotime(date('Ymd', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])));
                        } else {
                            $date_conds['$gte'] = strtotime($v);
                        }
                    }

                    if (isset($sections[1]) && $sections[1]) {
                        $v = $sections[1];
                        $unit = substr($v, -1);
                        if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                            $direct = substr($v, 0, 1);
                            $vv = abs(substr($v, 0, -1));
                            $date_conds['$lte'] = strtotime(date('Y-m-d', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])) . ' 23:59:59');
                        } else {
                            $date_conds['$lte'] = strtotime($v . ' 23:59:59');
                        }
                    }
                    $and[] = [
                        $key => $date_conds,
                    ];
                }
            } elseif (in_array($key_type_fields[$key], ['Text', 'TextArea', 'RichTextEditor', 'Url'])) {
                $and[] = [$key => ['$regex' => $val]];
            } elseif (in_array($key_type_fields[$key], ['Number', 'Integer'])) {
                if (strpos($val, '~') !== false) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $and[] = [$key => ['$gte' => $sections[0] + 0]];
                    }
                    if ($sections[1]) {
                        $and[] = [$key => ['$lte' => $sections[1] + 0]];
                    }
                }
            } elseif ($key_type_fields[$key] === 'TimeTracking') {
                if (strpos($val, '~') !== false) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $and[] = [$key . '_m' => ['$gte' => $this->ttHandleInM($sections[0])]];
                    }
                    if ($sections[1]) {
                        $and[] = [$key . '_m' => ['$lte' => $this->ttHandleInM($sections[1])]];
                    }
                }
            }
        }

        if (isset($query['watcher']) && $query['watcher']) {
            $watcher = $query['watcher'] == 'me' ? get_user_id() : $query['watcher'];
            $watched_issues = di()->get(WatchService::class)->getByProjectKeyAndWatcher(get_project_key(), $watcher);
            $watched_issue_ids = array_column($watched_issues, 'issue_id');
            $watchedIds = [];
            foreach ($watched_issue_ids as $id) {
                $watchedIds[] = $id;
            }
            $and[] = ['_id' => ['$in' => $watchedIds]];
        }
        $and[] = ['del_flg' => ['$ne' => 1]];

        return ['$and' => $and];
    }
}
