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

use App\Event\LabelEvent;
use App\Event\VersionEvent;
use App\Service\IssueService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

#[Listener]
class FlushCacheListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            VersionEvent::class,
            LabelEvent::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof VersionEvent) {
            $version = $event->getVersion();
            di()->get(IssueService::class)->putOptionsAsync($version->project_key);
        }

        if ($event instanceof LabelEvent) {
            $label = $event->getLabel();
            di()->get(IssueService::class)->putOptionsAsync($label->project_key);
        }
    }
}
