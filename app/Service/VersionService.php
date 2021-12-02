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
namespace App\Service;

use App\Model\Project;
use App\Service\Dao\ConfigFieldDao;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\VersionFormatter;
use Han\Utils\Service;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class VersionService extends Service
{
    #[Inject]
    public VersionDao $dao;

    #[Inject]
    public VersionFormatter $formatter;

    public function index(Project $project, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        [$count, $versionModels] = $this->dao->index($project->key, $limit, $offset);
        $versionFieldModels = di(ConfigFieldDao::class)->getFieldList($project->key);

        $result = [];
        foreach ($versionModels as $versionModel) {
            $item = $this->formatter->base($versionModel);
        }
    }
}
