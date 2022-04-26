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

use App\Service\Dao\IssueDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\UserDao;
use App\Service\Struct\Workflow;
use Han\Utils\Service;

class WebHookService extends Service
{
    public function handle(string $email, array $actions): void
    {
        $user = di()->get(UserDao::class)->firstByEmail($email);
        if (empty($user)) {
            return;
        }

        foreach ($actions as $item) {
            $action = $item['action'] ?? null;
            $project = $item['project'] ?? null;
            $no = $item['no'] ?? null;
            if ($action && $project && $no) {
                $project = di()->get(ProjectDao::class)->firstByKey($project);
                if ($project && $issue = di()->get(IssueDao::class)->firstByProjectKey($project->key, (int) $no)) {
                    ProjectAuth::instance()->setCurrent($project);

                    $wf = new Workflow($issue->entry);

                    $wfactions = $wf->getAvailableActions([
                        'project_key' => $issue->project_key,
                        'issue_id' => $issue->id,
                        'caller' => $user->id,
                    ], true);

                    $actionId = null;
                    foreach ($wfactions as $wfaction) {
                        if (($wfaction['state'] ?? null) == $action || ($wfaction['id'] ?? null) == $action || ($wfaction['name'] ?? null) == $action) {
                            $actionId = $wfaction['id'];
                            break;
                        }
                    }

                    if ($actionId) {
                        $wf->doAction(
                            $actionId,
                            [
                                'project_key' => $project->key,
                                'issue_id' => $issue->id,
                                'issue' => $issue,
                                'caller' => $user->toSmall(),
                            ]
                        );

                        $issue->pushToSearch();
                    }
                }
            }
        }
    }
}
