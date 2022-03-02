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
use App\Service\Dao\BoardDao;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class BoardService extends Service
{
    #[Inject]
    protected BoardDao $dao;

    public function getByProjectKey(Project $projectKey)
    {
        return $this->dao->getByProjectKey($projectKey);
    }
}
