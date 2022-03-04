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

use App\Service\Dao\AccessBoardLogDao;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use Hyperf\Di\Annotation\Inject;

class AccessBoardLogService extends Service
{
    #[Inject]
    protected AccessBoardLogDao $dao;

    public function getByProjectKeyAndUserId(string $projectKey, int $userId): Collection
    {
        return $this->dao->getByProjectKeyAndUserId($projectKey, $userId);
    }
}
