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
use App\Service\Dao\LabelDao;
use App\Service\Formatter\LabelFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class LabelService extends Service
{
    #[Inject]
    protected LabelDao $dao;

    #[Inject]
    protected LabelFormatter $formatter;

    public function findByProject(Project $project)
    {
        $models = $this->dao->getLabelOptions($project->key);

        return $this->formatter->formatList($models);
    }

    public function createOrUpdate(string $name, string $projectKey, ?string $bgColor, int $id = 0): bool
    {
        return $this->dao->createOrUpdate($name, $projectKey, $bgColor, $id);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
