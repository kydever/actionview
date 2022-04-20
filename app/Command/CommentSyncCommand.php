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
namespace App\Command;

use App\Model\Issue;
use App\Service\Dao\CommentDao;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class CommentSyncCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('sync:comment');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('同步评论数量到问题');
    }

    public function handle()
    {
        $this->info('准备同步评论数量，请稍后。。。');
        $issues = Issue::all();
        $bar = $this->output->createProgressBar(count($issues));
        $bar->start();
        foreach ($issues as $issue) {
            $count = count(di(CommentDao::class)->findByIssueId($issue->id));
            $issue->comments_num = $count;
            $issue->save();
            $issue->pushToSearch();
            $bar->advance();
        }
        $bar->finish();
        echo PHP_EOL;
        $this->info('同步完成！');
    }
}
