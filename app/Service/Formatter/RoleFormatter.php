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
namespace App\Service\Formatter;

use App\Model\AclRole;
use Han\Utils\Service;

class RoleFormatter extends Service
{
    public function base(AclRole $model)
    {
        return [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'name' => $model->name,
        ];
    }
}
