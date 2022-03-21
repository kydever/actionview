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

use App\Model\Activity;
use App\Service\Dao\IssueDao;
use Han\Utils\Service;

class ActivityService extends Service
{
    public function index(array $attributes)
    {
        $query = Activity::projectKey(get_project_key());
        $category = $attributes['category'] ?? 'all';
        if ($category != 'all') {
            $query->eventKey($category);
        }
        $offsetId = $attributes['offset_id'] ?? null;
        if (! is_null($offsetId)) {
            $query->where('id', '<', $offsetId);
        }
        $query->orderBy('id', 'desc');

        $limit = $attributes['limit'] ?? null;
        if (is_null($limit)) {
            $limit = 30;
        }
        $query->take($limit);
        $avatars = [];
        $activities = $query->get();
        foreach ($activities as $key => $activity) {
            if (! array_key_exists($activity['user']['id'], $avatars)) {
                $user = UserAuth::instance()->getUser();
                $avatars[$activity['user']['id']] = $user->avatar ?? '';
            }
            $activities[$key]['user']['avatar'] = $avatars[$activity['user']['id']];
            if ($activity['event_key'] == 'create_link' || $activity['event_key'] == 'del_link') {
                $activity[$key]['issue_link'] = [];
                $cacheIssues = $cache_issues[$activity['issue_id']] ?? null;
                if (! is_null($cacheIssues)) {
                    $issue = $cacheIssues;
                } else {
                    $issue = di()->get(IssueDao::class)->firstByProjectKey(get_project_key());
                }
                $activities[$key]['issue_link']['src'] = [
                    'id' => $activity['issue_id'],
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
                $activities[$key]['issue_link']['relation'] = $activity['data']['relation'];

                $cacheIssues = $cache_issues[$activity['data']['dest']] ?? null;
                if (! is_null($cacheIssues)) {
                    $issue = $cacheIssues;
                } else {
                    $issue = di()->get(IssueDao::class)->first($activity['data']['dest']);
                }
                $activities[$key]['issue_link']['dest'] = [
                    'id' => $activity['data']['dest'],
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
            } elseif (isset($activity['issue_id'])) {
                $cacheIssues = $cache_issues[$activity['issue_id']];
                if (! is_null($cacheIssues)) {
                    $issue = $cacheIssues;
                } else {
                    $issue = di()->get(IssueDao::class)->first($activity['issue_id']);
                }
                $activities[$key]['issue'] = [
                    'id' => $activity['issue_id'],
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
                $cache_issues[$activity['issue_id']] = $issue;
            }
        }

        return $activities;
    }
}
