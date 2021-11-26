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
use App\Service\Dao\AccessProjectLogDao;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Formatter\ProjectFormatter;
use App\System\Eloquent\SysSetting;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ProjectService extends Service
{
    #[Inject]
    protected ProjectDao $dao;

    #[Inject]
    protected AclService $acl;

    #[Inject]
    protected ProjectFormatter $formatter;

    public function getLatestAccessProject(int $userId): ?Project
    {
        $model = di()->get(AccessProjectLogDao::class)->latest($userId);
        if ($model?->project?->isActive()) {
            return $model->project;
        }

        return null;
    }

    public function mine(int $userId, string $sortkey)
    {
        $keys = $this->getRecentProjectKeys($userId);

        if (isset($sortkey) && $sortkey) {
            $pkey_cnts = [];
            if ($sortkey == 'all_issues_cnt') {
                foreach ($pkeys as $pkey) {
                    $pkey_cnts[$pkey] = DB::collection('issue_' . $pkey)
                        ->where('del_flg', '<>', 1)
                        ->count();
                }
            } elseif ($sortkey == 'unresolved_issues_cnt') {
                foreach ($pkeys as $pkey) {
                    $pkey_cnts[$pkey] = DB::collection('issue_' . $pkey)
                        ->where('del_flg', '<>', 1)
                        ->where('resolution', 'Unresolved')
                        ->count();
                }
            } elseif ($sortkey == 'assigntome_issues_cnt') {
                foreach ($pkeys as $pkey) {
                    $pkey_cnts[$pkey] = DB::collection('issue_' . $pkey)
                        ->where('del_flg', '<>', 1)
                        ->where('resolution', 'Unresolved')
                        ->where('assignee.id', $this->user->id)
                        ->count();
                }
            } elseif ($sortkey == 'activity') {
                $twoWeeksAgo = strtotime(date('Ymd', strtotime('-2 week')));
                foreach ($pkeys as $pkey) {
                    $pkey_cnts[$pkey] = DB::collection('activity_' . $pkey)
                        ->where('created_at', '>=', $twoWeeksAgo)
                        ->count();
                }
            } elseif ($sortkey == 'key_asc') {
                sort($pkeys);
            } elseif ($sortkey == 'key_desc') {
                rsort($pkeys);
            } elseif ($sortkey == 'create_time_asc') {
                $project_keys = \App\Project\Eloquent\Project::whereIn('key', $pkeys)
                    ->orderBy('created_at', 'asc')
                    ->get(['key'])
                    ->toArray();
                $pkeys = array_column($project_keys, 'key');
            } elseif ($sortkey == 'create_time_desc') {
                $project_keys = Project::whereIn('key', $pkeys)
                    ->orderBy('created_at', 'desc')
                    ->get(['key'])
                    ->toArray();
                $pkeys = array_column($project_keys, 'key');
            }

            if ($pkey_cnts) {
                arsort($pkey_cnts);
                $pkeys = array_keys($pkey_cnts);
            }
        }

        $offset_key = $request->input('offset_key');
        if (isset($offset_key)) {
            $ind = array_search($offset_key, $pkeys);
            if ($ind === false) {
                $pkeys = [];
            } else {
                $pkeys = array_slice($pkeys, $ind + 1);
            }
        }

        $limit = $request->input('limit');
        if (! isset($limit)) {
            $limit = 24;
        }
        $limit = intval($limit);

        $status = $request->input('status');
        if (! isset($status)) {
            $status = 'all';
        }

        $name = $request->input('name');

        $projects = [];
        foreach ($pkeys as $pkey) {
            $query = Project::where('key', $pkey);
            if ($name) {
                $query->where(function ($query) use ($name) {
                    $query->where('key', 'like', '%' . $name . '%')->orWhere('name', 'like', '%' . $name . '%');
                });
            }
            if ($status != 'all') {
                $query = $query->where('status', $status);
            }

            $project = $query->first();
            if (! $project) {
                continue;
            }

            $projects[] = $project->toArray();
            if (count($projects) >= $limit) {
                break;
            }
        }

        foreach ($projects as $key => $project) {
            $projects[$key]['principal']['nameAndEmail'] = $project['principal']['name'] . '(' . $project['principal']['email'] . ')';
        }

        $syssetting = SysSetting::first();
        $allow_create_project = isset($syssetting->properties['allow_create_project']) ? $syssetting->properties['allow_create_project'] : 0;

        return Response()->json(['ecode' => 0, 'data' => $projects, 'options' => ['limit' => $limit, 'allow_create_project' => $allow_create_project]]);
    }

    public function recent(int $userId)
    {
        $keys = $this->getRecentProjectKeys($userId);
        $projects = di()->get(ProjectDao::class)->findByKeys($keys);
        $result = [];
        foreach ($projects as $project) {
            if (! $project->isActive()) {
                continue;
            }

            $result[] = $this->formatter->small($project);

            if (count($result) >= 5) {
                break;
            }
        }

        return $result;
    }

    public function getRecentProjectKeys(int $userId): array
    {
        $groupIds = di()->get(AclGroupDao::class)->findByUserId($userId)->columns('id')->toArray();
        $projectKeys = di()->get(UserGroupProjectDao::class)->findByUGIds([$userId, ...$groupIds])->columns('project_key')->toArray();
        $accessedProjectKeys = di()->get(AccessProjectLogDao::class)->findLatestProjectKeys($userId);

        return array_values(array_unique(array_intersect($projectKeys, $accessedProjectKeys)));
    }
}
