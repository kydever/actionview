<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ProviderService;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
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
        $modules = di(ProviderService::class)->getModuleList('');
        $epics = di(ProviderService::class)->getEpicList('');
    }
}
