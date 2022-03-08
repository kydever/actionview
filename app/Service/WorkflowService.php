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

use App\Model\OswfDefinition;
use App\Model\User;
use App\Service\Dao\OswfDefinitionDao;
use App\Service\Formatter\DefinitionFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class WorkflowService extends Service
{
    #[Inject]
    protected OswfDefinitionDao $dao;

    #[Inject]
    protected DefinitionFormatter $formatter;

    public function preview(int $id)
    {
        $model = $this->dao->first($id, true);

        return $this->formatter->base($model);
    }

    public function save(int $id, User $user, string $projectKey, array $attributes): OswfDefinition
    {
        return $this->dao->createOrUpdate($id, $user, $projectKey, $attributes);
    }
}
