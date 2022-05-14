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
namespace App\Listener;

use App\Model\Issue;
use App\Service\Dao\ConfigStateDao;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\IssueHistoryDao;
use Carbon\Carbon;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\ModelListener\Annotation\ModelListener;
use Hyperf\Utils\Arr;

#[ModelListener(models: [Issue::class])]
class IssueUpdateListener
{
    public function created(Created $event)
    {
        /** @var Issue $issue */
        $issue = $event->getModel();

        di(IssueHistoryDao::class)->create([
            'project_key' => $issue->project_key,
            'issue_id' => $issue->id,
            'operator' => $issue->modifier,
        ]);
    }

    public function updated(Updated $event)
    {
        /** @var Issue $issue */
        $issue = $event->getModel();
        $now = Carbon::now();
        $modifier = $issue->modifier;

        $dirty = $issue->getDirty();
        $dirty = Arr::only($dirty, [
            'type', // 类型
            'resolution', // 解决状态
            'assignee', // 负责人
            'reporter', // 报告人
            'data', // 具体数据
        ]);

        $data = [];
        foreach ($dirty as $key => $value) {
            $item = match ($key) {
                'type' => $this->formatTypeData($issue),
                'resolution' => $this->formatResolutionData($issue),
                'assignee', 'reporter' => $this->formatUserData($issue, $key),
                'data' => $this->formatDate($issue),
                default => null,
            };

            if ($item) {
                if (! array_is_list($item)) {
                    $item = [$item];
                }

                $data = array_merge($data, $item);
            }
        }

        di(IssueHistoryDao::class)->create([
            'project_key' => $issue->project_key,
            'issue_id' => $issue->id,
            'operation' => 'modify',
            'operator' => $issue->modifier,
            'data' => $data,
        ]);
    }

    protected function formatDate(Issue $issue): array
    {
        $original = $issue->getOriginal('data');
        $data = $issue->data;

        $result = [];
        foreach ($data as $key => $value) {
            if (($original[$key] ?? null) != $value) {
                $field = str_replace(
                    ['type', 'title', 'priority', 'assignee', 'module', 'descriptions', 'attachments', 'epic', 'expect_start_time', 'expect_complete_time', 'progress', 'original_estimate', 'story_points', 'resolve_version', 'labels', 'related_users', 'state'],
                    ['类型', '主题', '优先级', '负责人', '模块', '描述', '附件', 'Epic', '计划开始时间', '计划完成时间', '进度', '原估时间', '故事点数', '解决版本', '标签', '关联用户', '状态'],
                    $key
                );

                if (! is_array($value)) {
                    $item = match ($key) {
                        'state' => $this->formatStateData($issue),
                        default => [
                            'field' => $field,
                            'before_value' => $original[$key] ?? '',
                            'after_value' => $value,
                        ]
                    };

                    if ($item) {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    protected function formatStateData(Issue $issue): ?array
    {
        $original = $issue->getOriginal('data')['state'] ?? '';
        $state = $issue->data['state'] ?? '';

        if ($original == $state) {
            return null;
        }

        if (is_numeric($original)) {
            $original = di()->get(ConfigStateDao::class)->findById((int) $original)?->name ?? '未知状态';
        } else {
            $original = $this->replaceState($original);
        }

        if (is_numeric($state)) {
            $state = di()->get(ConfigStateDao::class)->findById((int) $state)?->name ?? '未知状态';
        } else {
            $state = $this->replaceState($state);
        }

        return [
            'field' => '状态',
            'before_value' => $original,
            'after_value' => $state,
        ];
    }

    protected function replaceState(string $status): string
    {
        return str_replace(
            ['Open', 'In Progess', 'Resolved', 'Closed', 'Reopened'],
            ['开始', '进行中', '已完成', '关闭', '重新打开'],
            $status
        );
    }

    protected function formatUserData(Issue $issue, string $key): ?array
    {
        $field = match ($key) {
            'assignee' => '负责人',
            'reporter' => '报告者',
        };

        return [
            'field' => $field,
            'before_value' => $issue->getOriginal($key)['name'],
            'after_value' => $issue->{$key}['name'],
        ];
    }

    protected function formatResolutionData(Issue $issue): ?array
    {
        return [
            'field' => '解决结果',
            'before_value' => $issue->getOriginal('resolution'),
            'after_value' => $issue->resolution,
        ];
    }

    protected function formatTypeData(Issue $issue): ?array
    {
        $oldValue = (int) $issue->getOriginal('type');
        $newValue = (int) $issue->type;
        if ($oldValue === $newValue) {
            return null;
        }

        $oldType = di()->get(ConfigTypeDao::class)->first($oldValue);
        $newType = di()->get(ConfigTypeDao::class)->first($newValue);
        return [
            'field' => '类型',
            'before_value' => $oldType->name,
            'after_value' => $newType->name,
        ];
    }
}
