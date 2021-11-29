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

use App\Project\Provider;
use Han\Utils\Service;

class ProjectSummaryService extends Service
{
    public function index(string $key)
    {
        // the top four filters
        $filters = $this->getTopFourFilters($project_key);
        // the two weeks issuepulse
        $trend = $this->getPulseData($project_key);

        $types = Provider::getTypeList($project_key);

        $optPriorities = [];
        $priorities = Provider::getPriorityList($project_key);
        foreach ($priorities as $priority) {
            if (isset($priority['key'])) {
                $optPriorities[$priority['key']] = $priority['name'];
            } else {
                $optPriorities[$priority['_id']] = $priority['name'];
            }
        }

        $optModules = [];
        $modules = Provider::getModuleList($project_key);
        foreach ($modules as $module) {
            $optModules[$module->id] = $module->name;
        }

        //$users = Provider::getUserList($project_key);

        $issues = DB::collection('issue_' . $project_key)
            ->where('created_at', '>=', strtotime(date('Ymd', strtotime('-1 week'))))
            ->where('del_flg', '<>', 1)
            ->get(['type']);

        $new_issues = ['total' => 0];
        foreach ($issues as $issue) {
            if (! isset($new_issues[$issue['type']])) {
                $new_issues[$issue['type']] = 0;
            }
            ++$new_issues[$issue['type']];
            ++$new_issues['total'];
        }

        $issues = DB::collection('issue_' . $project_key)
            ->where('state', 'Closed')
            ->where('updated_at', '>=', strtotime(date('Ymd', strtotime('-1 week'))))
            ->where('del_flg', '<>', 1)
            ->get(['type']);

        $closed_issues = ['total' => 0];
        foreach ($issues as $issue) {
            if (! isset($closed_issues[$issue['type']])) {
                $closed_issues[$issue['type']] = 0;
            }
            ++$closed_issues['total'];
            ++$closed_issues[$issue['type']];
        }

        $new_percent = $closed_percent = 0;
        if ($new_issues['total'] > 0 || $closed_issues['total'] > 0) {
            $new_percent = $new_issues['total'] * 100 / ($new_issues['total'] + $closed_issues['total']);
            if ($new_percent > 0 && $new_percent < 1) {
                $new_percent = 1;
            } else {
                $new_percent = floor($new_percent);
            }
            $closed_percent = 100 - $new_percent;
        }

        $new_issues['percent'] = $new_percent;
        $closed_issues['percent'] = $closed_percent;

        $issues = DB::collection('issue_' . $project_key)
            ->where('resolution', 'Unresolved')
            ->where('del_flg', '<>', 1)
            ->get(['priority', 'assignee', 'type', 'module']);

        $users = [];
        $assignee_unresolved_issues = [];
        foreach ($issues as $issue) {
            if (! isset($issue['assignee']) || ! $issue['assignee']) {
                continue;
            }

            $users[$issue['assignee']['id']] = $issue['assignee']['name'];
            if (! isset($assignee_unresolved_issues[$issue['assignee']['id']][$issue['type']])) {
                $assignee_unresolved_issues[$issue['assignee']['id']][$issue['type']] = 0;
            }
            if (! isset($assignee_unresolved_issues[$issue['assignee']['id']]['total'])) {
                $assignee_unresolved_issues[$issue['assignee']['id']]['total'] = 0;
            }
            ++$assignee_unresolved_issues[$issue['assignee']['id']][$issue['type']];
            ++$assignee_unresolved_issues[$issue['assignee']['id']]['total'];
        }
        $assignee_unresolved_issues = $this->calPercent($assignee_unresolved_issues);

        $priority_unresolved_issues = [];
        foreach ($issues as $issue) {
            if (! isset($issue['priority']) || ! $issue['priority']) {
                $priority_id = '-1';
            } else {
                $priority_id = isset($optPriorities[$issue['priority']]) ? $issue['priority'] : '-1';
            }

            if (! isset($priority_unresolved_issues[$priority_id][$issue['type']])) {
                $priority_unresolved_issues[$priority_id][$issue['type']] = 0;
            }
            if (! isset($priority_unresolved_issues[$priority_id]['total'])) {
                $priority_unresolved_issues[$priority_id]['total'] = 0;
            }
            ++$priority_unresolved_issues[$priority_id][$issue['type']];
            ++$priority_unresolved_issues[$priority_id]['total'];
        }

        $sorted_priority_unresolved_issues = [];
        foreach ($optPriorities as $key => $val) {
            if (isset($priority_unresolved_issues[$key])) {
                $sorted_priority_unresolved_issues[$key] = $priority_unresolved_issues[$key];
            }
        }
        if (isset($priority_unresolved_issues['-1'])) {
            $sorted_priority_unresolved_issues['-1'] = $priority_unresolved_issues['-1'];
        }

        $sorted_priority_unresolved_issues = $this->calPercent($sorted_priority_unresolved_issues);

        $module_unresolved_issues = [];
        foreach ($issues as $issue) {
            $module_ids = [];
            if (! isset($issue['module']) || ! $issue['module']) {
                $module_ids = ['-1'];
            } else {
                $ms = is_string($issue['module']) ? explode(',', $issue['module']) : $issue['module'];
                foreach ($ms as $m) {
                    $module_ids[] = isset($optModules[$m]) ? $m : '-1';
                }
                $module_ids = array_unique($module_ids);
            }

            foreach ($module_ids as $module_id) {
                if (count($module_ids) > 1 && $module_id === '-1') {
                    continue;
                }
                if (! isset($module_unresolved_issues[$module_id][$issue['type']])) {
                    $module_unresolved_issues[$module_id][$issue['type']] = 0;
                }
                if (! isset($module_unresolved_issues[$module_id]['total'])) {
                    $module_unresolved_issues[$module_id]['total'] = 0;
                }
                ++$module_unresolved_issues[$module_id][$issue['type']];
                ++$module_unresolved_issues[$module_id]['total'];
            }
        }

        $sorted_module_unresolved_issues = [];
        foreach ($optModules as $key => $val) {
            if (isset($module_unresolved_issues[$key])) {
                $sorted_module_unresolved_issues[$key] = $module_unresolved_issues[$key];
            }
        }
        if (isset($module_unresolved_issues['-1'])) {
            $sorted_module_unresolved_issues['-1'] = $module_unresolved_issues['-1'];
        }

        $sorted_module_unresolved_issues = $this->calPercent($sorted_module_unresolved_issues);

        return Response()->json([
            'ecode' => 0,
            'data' => [
                'filters' => $filters,
                'trend' => $trend,
                'new_issues' => $new_issues,
                'closed_issues' => $closed_issues,
                'assignee_unresolved_issues' => $assignee_unresolved_issues,
                'priority_unresolved_issues' => $sorted_priority_unresolved_issues,
                'module_unresolved_issues' => $sorted_module_unresolved_issues, ],
            'options' => [
                'types' => $types,
                'users' => $users,
                'priorities' => $optPriorities,
                'modules' => $optModules,
                'twoWeeksAgo' => date('m/d', strtotime('-2 week')),
            ],
        ]);
    }

    /**
     * get the top four filters info.
     *
     * @param string $project_key
     * @return array
     */
    public function getTopFourFilters($project_key)
    {
        $filters = Provider::getIssueFilters($project_key, $this->user->id);
        $filters = array_slice($filters, 0, 4);
        foreach ($filters as $key => $filter) {
            $query = [];
            if (isset($filter['query']) && $filter['query']) {
                $query = $filter['query'];
            }

            $where = $this->getIssueQueryWhere($project_key, $query);
            $count = DB::collection('issue_' . $project_key)
                ->where($where)
                ->count();

            $filters[$key]['count'] = $count;
        }

        return $filters;
    }
}
