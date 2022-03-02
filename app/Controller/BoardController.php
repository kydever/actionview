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
namespace App\Controller;

use App\Service\AccessBoardLogService;
use App\Service\BoardService;
use App\Service\EpicService;
use App\Service\Formatter\BoardFormatter;
use App\Service\Formatter\EpicFormatter;
use App\Service\Formatter\SprintFormatter;
use App\Service\Formatter\VersionFormatter;
use App\Service\ProjectAuth;
use App\Service\SprintService;
use App\Service\UserAuth;
use App\Service\VersionService;
use Hyperf\Di\Annotation\Inject;

class BoardController extends Controller
{
    #[Inject]
    protected BoardService $service;

    #[Inject]
    protected BoardFormatter $formatter;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();
        $boards = $this->service->getByProjectKey($project);
        /** @var AccessBoardLogService $access_records */
        $access_records = di(AccessBoardLogService::class)->getByProjectKeyAndUserId($project, $user->id);

        $list = [];
        $accessed_boards = [];

        foreach ($access_records as $record) {
            foreach ($boards as $board) {
                if ($board->id == $record->board_id) {
                    $accessed_boards[] = $record->board_id;
                    break;
                }
            }
            if (in_array($record->board_id, $accessed_boards)) {
                $list[] = $board;
            }
        }

        foreach ($boards as $board) {
            if (! in_array($board->id, $accessed_boards)) {
                $list[] = $board;
            }
        }

        /** @var SprintService $sprintService */
        $sprintService = di(SprintService::class);
        $sprints = $sprintService->getByProjectKeyAndStatus($project);
        foreach ($sprints as $sprint) {
            if (! $sprint->name) {
                $sprint->name = 'Sprint ' . $sprint->no;
            }
        }

        /** @var EpicService $epics */
        $epics = di(EpicService::class)->getByProjectKey($project);

        /** @var VersionService $versions */
        $versions = di(VersionService::class)->getByProjectKey($project);

        $completed_sprint_num = $sprintService->maxByProjectKeyAndStatus($project);

        return $this->response->success([
            $this->formatter->listFormat($list),
            'options' => [
                'epics' => di(EpicFormatter::class)->formatList($epics),
                'sprints' => di(SprintFormatter::class)->formatList($sprints),
                'versions' => di(VersionFormatter::class)->formatList($versions),
                'completed_sprint_num' => $completed_sprint_num,
            ],
        ]);
    }
}
