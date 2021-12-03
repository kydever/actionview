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
use App\Service\Dao\VersionDao;
use App\Service\Formatter\VersionFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class VersionService extends Service
{
    #[Inject]
    protected VersionDao $dao;

    #[Inject]
    protected VersionFormatter $formatter;

    #[Inject]
    protected ProviderService $provider;

    public function index(Project $project, int $offset, int $limit): array
    {
        [$count, $models] = $this->dao->index($project->key, $offset, $limit);

        // TODO: 从搜索引擎中查询对应数量
        // $versionIds = $models->columns('id')->toArray();
        // $versionFieldModels = $this->provider->getFieldList($project->key);

        $result = $this->formatter->formatList($models);

        $options = ['total' => $count, 'sizePerPage' => $limit, 'current_time' => time()];

        return [$result, $options];
    }
}
