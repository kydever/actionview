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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\DbConnection\Db;
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
        $unresolvedModels = Db::select('select resolve_version, count(*) as num from issue where project_key = "p_open_source" and del_flg != 1 and resolution = "Unresolved" group by resolve_version');
        $unresolved = [];
        foreach ($unresolvedModels as $unresolvedModel) {
            $unresolved[$unresolvedModel->resolve_version] = $unresolvedModel->num;
        }
        var_dump($unresolved);
    }
}
