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
use Hyperf\Database\Model\Events\Updated;
use Hyperf\ModelListener\Annotation\ModelListener;

#[ModelListener(models: [Issue::class])]
class IssueUpdateListener
{
    public function updated(Updated $event)
    {
        /** @var Issue $model */
        $model = $event->getModel();
        defer(static function () use ($model) {
            $model->pushToSearch();
        });
    }
}
