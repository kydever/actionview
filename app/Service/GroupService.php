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

use App\Service\Dao\AclGroupDao;
use App\Service\Formatter\GroupFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class GroupService extends Service
{
    #[Inject]
    protected AclGroupDao $dao;

    #[Inject]
    protected GroupFormatter $formatter;

    public function index(array $input, int $offset, int $limit)
    {
        [$total, $models] = $this->dao->find($input, $offset, $limit);

        $models->load('userModels');

        $result = $this->formatter->formatList($models);

        return [$total, $result];
    }
}
