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
use App\Service\Client\IssueSearch;
use Han\Utils\ElasticSearch;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class PutMappingToSearchCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('put:mapping');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建搜索引擎索引');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, '是否强制创建');
        $this->addOption('index', 'i', InputOption::VALUE_OPTIONAL, '需要初始化的索引');
    }

    public function handle()
    {
        $force = $this->input->getOption('force');
        $index = $this->input->getOption('index');
        if ($index === null) {
            $index = $this->choice('请选择需要创建的索引', [
                'issue' => '问题索引',
            ]);
        }

        /** @var ElasticSearch $search */
        $search = match ($index) {
            'issue' => di()->get(IssueSearch::class),
            default => throw new BusinessException(ErrorCode::SERVER_ERROR, '不存在当前 SearchClient'),
        };

        $search->putIndex($force);
        $search->putMapping();

        $this->output->writeln('创建索引完毕');
    }
}
