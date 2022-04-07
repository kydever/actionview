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

use App\Event\DeleteGroupEvent;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\GroupService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

#[Listener]
class DeleteGroupListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            DeleteGroupEvent::class,
        ];
    }

    /**
     * @param DeleteGroupEvent $event
     */
    public function process(object $event): void
    {
        $this->deleteGroupFromRole($event->getGroupId());
        $this->deleteGroupProject($event->getGroupId());

        di()->get(GroupService::class)->putAll();
    }

    protected function deleteGroupProject(int $groupId)
    {
        $models = di()->get(UserGroupProjectDao::class)->findByGroupId($groupId);
        foreach ($models as $model) {
            $model->delete();
        }
    }

    protected function deleteGroupFromRole(int $groupId)
    {
        $actors = di()->get(AclRoleactorDao::class)->findByGroupId($groupId);
        foreach ($actors as $actor) {
            $groupIds = [];
            foreach ($actor->group_ids as $id) {
                if ($id != $groupId) {
                    $groupIds[] = $id;
                }
            }

            $actor->group_ids = $groupIds;
            $actor->save();
        }
    }
}
