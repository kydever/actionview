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

use App\Model\Label;
use App\Model\Project;
use App\Service\Client\IssueSearch;
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

    public function findByProject(Project $project): array
    {
        $models = $this->dao->getLabelOptions($project->key);

        $labelsCount = di()->get(IssueSearch::class)->countByLabels($project->key);

        return $this->formatter->formatListWithCount($models, $labelsCount);
    }

    public function save(int $id, Project $project, string $name, ?string $bgColor): Label
    {
        return $this->dao->createOrUpdate($id, $project->key, $name, $bgColor);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
