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

use App\Constants\IssueFiltersConstant;
use App\Service\Dao\IssueFilterDao;
use App\Service\Formatter\IssueFilterFormatter;
use App\Service\ProviderService;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class TestCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('test:test');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
//        $modules = di(ProviderService::class)->getModuleList('$_sys_$');
//        $epics = di(ProviderService::class)->getEpicList('$_sys_$');
//        $versions = di(ProviderService::class)->getVersionList('$_sys_$');
//        $labels = di(ProviderService::class)->getLabelOptions('$_sys_$');
//        $type = di(ProviderService::class)->getTypeListExt('$_sys_$');
//        $field = di(ProviderService::class)->getFieldList('$_sys_$');
//        $customizeFilterModels = di(IssueFilterDao::class)->getIssueFilters('$_sys_$', 2);
        $filters = IssueFiltersConstant::DEFAULT_ISSUE_FILTERS;
        $customizeFilterModels = di(IssueFilterDao::class)->getIssueFilters('$_sys_$', 1);
        $customizeFilters = di(IssueFilterFormatter::class)->formatList($customizeFilterModels);
        $filters = array_merge($filters, $customizeFilters);
        var_dump($filters);
    }
}
