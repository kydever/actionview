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

use App\Service\Dao\EpicDao;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use Hyperf\Di\Annotation\Inject;

class EpicService extends Service
{
    #[Inject]
    protected EpicDao $dao;

    public function getByProjectKey(string $projectKey): Collection
    {
        return $this->dao->getByProjectKey($projectKey);
    }
}
