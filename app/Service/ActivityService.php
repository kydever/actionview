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
use App\Model\Activity;
use App\Service\Dao\ActivityDao;
use App\Service\Dao\IssueDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\ActivityFormatter;
use App\Service\Formatter\IssueFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ActivityService extends Service
{
    #[Inject]
    protected ActivityDao $dao;

    #[Inject]
    protected ActivityFormatter $formatter;

    public function index(string $key, array $attributes): array
    {
        $cacheIssues = [];

        $query = $this->dao->whereBy('project_key', $key);

        $category = $attributes['category'] ?? null;
        if ($category != 'all') {
            $query->where('event_key', 'like', "%{$category}");
        }

        $offsetId = $attributes['offset_id'] ?? null;
        if ($offsetId) {
            $query->where('id', '<', $offsetId);
        }

        $query->orderByDesc('id');

        $limit = $attributes['limit'] ?? null;
        if (is_null($limit)) {
            $limit = 30;
        }

        $query->limit((int) $limit);

        $avatars = [];

        $activities = $query->get();
        foreach ($activities as &$activity) {
            $userId = $activity['user']['id'] ?? null;
            if (! array_key_exists($userId, $avatars)) {
                $user = di(UserDao::class)->first($userId);
                $avatars[$userId] = $user->avatar ?? '';
            }
            $activity['avatar'] = $avatars[$userId];

            if (($activity['event_key'] ?? null) == 'create_link' || ($activity['event_key'] ?? null) == 'del_link') {
                $issueLink = &$activity['issue_link'];

                $issueId = $activity['issue_id'] ?? null;
                if (isset($cacheIssues[$issueId])) {
                    $issue = $cacheIssues[$issueId];
                } else {
                    $model = di(IssueDao::class)->first($issueId, true);
                    $issue = di(IssueFormatter::class)->base($model);
                }

                $issueLink['src'] = [
                    'id' => $issueId,
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
                $issueLink['relation'] = $activity['data']['relation'] ?? null;

                $dataDest = $activity['data']['dest'] ?? null;
                if (isset($cacheIssues[$dataDest])) {
                    $issue = $cacheIssues[$dataDest];
                } else {
                    $model = di(IssueDao::class)->first($dataDest, true);
                    $issue = di(IssueFormatter::class)->base($model);
                }
                $issueLink['dest'] = [
                    'id' => $dataDest,
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
            } elseif (isset($activity['issue_id'])) {
                $issueId = $activity['issue_id'] ?? null;
                if (isset($cacheIssues[$issueId])) {
                    $issue = $cacheIssues[$issueId];
                } else {
                    $model = di(IssueDao::class)->first($issueId);
                    $issue = di(IssueFormatter::class)->base($model);
                }
                $activity['issue'] = [
                    'id' => $issueId,
                    'no' => $issue['no'],
                    'title' => $issue['title'] ?? '',
                    'state' => $issue['state'] ?? '',
                    'del_flg' => $issue['del_flg'] ?? 0,
                ];
                $cacheIssues[$issueId] = $issue;
            }
        }

        return $this->formatter->formatList($activities);
    }

    public function create(array $attributes): Activity
    {
        if (! $this->checkRequiredParameters([
            'projectKey',
            'data',
            'eventKey',
            'issue',
            'issueId',
            'user',
        ], $attributes)) {
            throw new BusinessException(ErrorCode::MISSING_PARAMETER);
        }
        $model = new Activity();
        $model->project_key = $attributes['projectKey'];
        $model->data = $attributes['data'];
        $model->event_key = $attributes['eventKey'];
        $model->issue = $attributes['issue'];
        $model->issue_id = $attributes['issueId'];
        $model->user = $attributes['user'];
        $model->save();

        return $model;
    }

    protected function checkRequiredParameters(array $required, array $params): bool
    {
        foreach ($required as $req) {
            if (! isset($params[$req])) {
                return false;
            }
        }

        return true;
    }
}
