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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Issue;
use App\Model\Searchable;
use App\Service\Client\IssueSearch;
use Han\Utils\ElasticSearch;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class PutDataToSearchCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('put:data');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('推送数据到搜索引擎');
        $this->addOption('id', 'I', InputOption::VALUE_OPTIONAL, '起始ID', 0);
        $this->addOption('num', 'N', InputOption::VALUE_OPTIONAL, '执行条数');
    }

    public function handle()
    {
        $id = (int) $this->input->getOption('id');
        $num = $this->input->getOption('num');
        if ($num !== null) {
            $num = (int) $num;
        }

        $index = $this->choice('请选择需要推送的数据', [
            'issue' => '问题数据',
        ]);

        /** @var ElasticSearch $search */
        $search = match ($index) {
            'issue' => di()->get(IssueSearch::class),
            default => throw new BusinessException(ErrorCode::SERVER_ERROR, '不存在当前 SearchClient'),
        };

        $query = match ($index) {
            'issue' => Issue::query(),
            default => throw new BusinessException(ErrorCode::SERVER_ERROR, '不存在当前模型'),
        };

        while (true) {
            $models = $query->where('id', '>', $id)->orderBy('id')->limit(100)->get();
            if ($models->isEmpty()) {
                break;
            }

            /** @var Searchable $model */
            foreach ($models as $model) {
                $id = $model->getId();
                if ($num !== null && $num-- <= 0) {
                    break 2;
                }

                $model->pushToSearch();
            }

            $this->output->writeln('同步数据至 ' . $id);
        }

        $this->output->writeln('同步数据完毕');
    }
}
