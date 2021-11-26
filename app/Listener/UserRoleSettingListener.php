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

use App\Event\AddUserToRoleEvent;
use App\Model\UserGroupProject;
use App\Service\Dao\UserGroupProjectDao;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

#[Listener]
class UserRoleSettingListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            AddUserToRoleEvent::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof AddUserToRoleEvent) {
            $this->linkUserWithProject($event->getUserIds(), $event->getProjectKey());
        }
    }

    public function linkUserWithProject(array $userIds, string $projectKey)
    {
        foreach ($userIds as $userId) {
            $link = di()->get(UserGroupProjectDao::class)->firstByUserId($userId, $projectKey);
            $link?->increment('link_count');
            if (empty($link)) {
                $model = new UserGroupProject();
                $model->ug_id = $userId;
                $model->project_key = $projectKey;
                $model->type = 'user';
                $model->link_count = 1;
                $model->save();
            }
        }
    }
}
