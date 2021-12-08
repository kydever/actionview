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
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\OswfEntry;
use Han\Utils\Service;

class OswfEntryDao extends Service
{
    public function first(int $id, bool $throw = false): ?OswfEntry
    {
        $model = OswfEntry::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::WORKFLOW_NOT_EXISTS, 'Entry not found.');
        }

        return $model;
    }
}
