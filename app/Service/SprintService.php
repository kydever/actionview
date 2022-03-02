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

use App\Service\Dao\SprintDao;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class SprintService extends Service
{
    #[Inject]
    protected SprintDao $dao;

    public function getByProjectKeyAndStatus(string $projectKey)
    {
        return $this->dao->getByProjectKeyAndStatus($projectKey);
    }

    public function maxByProjectKeyAndStatus(string $projectKey)
    {
        return $this->dao->maxByProjectKeyAndStatus($projectKey);
    }
}
