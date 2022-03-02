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
use App\Model\User;
use App\Service\Dao\BoardDao;
use App\Service\Formatter\BoardFormatter;
use App\Service\Formatter\EpicFormatter;
use App\Service\Formatter\SprintFormatter;
use App\Service\Formatter\VersionFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class BoardService extends Service
{
    #[Inject]
    protected BoardDao $dao;

    #[Inject]
    protected BoardFormatter $formatter;

    public function index(User $user, Project $project)
    {
        $boards = $this->dao->getByProjectKey($project->key);
        $records = di(AccessBoardLogService::class)->getByProjectKeyAndUserId($project->key, $user->id);

        $list = [];
        $accessedBoardIds = [];

        foreach ($records as $record) {
            foreach ($boards as $board) {
                if ($board->id == $record->board_id) {
                    $accessedBoardIds[] = $record->board_id;
                    break;
                }
            }
        }

        foreach ($boards as $board) {
            if (! in_array($board->id, $accessedBoardIds)) {
                $list[] = $board;
            }
        }

        $sprintService = di(SprintService::class);
        $sprints = $sprintService->getByProjectKeyAndStatus($project->key);
        foreach ($sprints as $sprint) {
            if (! $sprint->name) {
                $sprint->name = 'Sprint ' . $sprint->no;
            }
        }

        $epics = di(EpicService::class)->getByProjectKey($project->key);

        $versions = di(VersionService::class)->getByProjectKey($project->key);

        $completedSprintNum = $sprintService->maxByProjectKeyAndStatus($project->key);

        return [
            $this->formatter->listFormat($list),
            [
                'epics' => di(EpicFormatter::class)->formatList($epics),
                'sprints' => di(SprintFormatter::class)->formatList($sprints),
                'versions' => di(VersionFormatter::class)->formatList($versions),
                'completed_sprint_num' => $completedSprintNum,
            ],
        ];
    }
}
