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

use App\Model\AclRoleactor;
use Han\Utils\Service;

class AclRoleactorFormatter extends Service
{
    public function base(AclRoleactor $model): array
    {
        return [
            'id' => $model->id,
            'role_id' => $model->role_id,
            'user_ids' => $model->user_ids,
            'group_ids' => $model->group_ids,
        ];
    }

    public function formatList($models): array
    {
        $results = [];
        foreach ($models as $model) {
            $results = $this->base($model);
        }

        return $results;
    }
}
