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

use App\Model\AclGroup;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class GroupFormatter extends Service
{
    public function base(AclGroup $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'users' => $model->users,
            'principal' => $model->principal,
            'public_scope' => $model->public_scope,
            'description' => $model->description,
            'directory' => $model->directory,
            'ldap_dn' => $model->ldap_dn,
            'sync_flag' => $model->sync_flag,
        ];
    }

    public function detail(AclGroup $model)
    {
        $result = $this->base($model);
        $result['users'] = di()->get(UserFormatter::class)->formatList($model->userModels);
        return $result;
    }

    /**
     * @param AclGroup[] $models
     */
    public function formatList(Collection $models)
    {
        $result = [];
        foreach ($models as $model) {
            $item = $this->base($model);
            if ($model->relationLoaded('userModels')) {
                $item['users'] = di()->get(UserFormatter::class)->formatList($model->userModels);
            }

            $result[] = $item;
        }

        return $result;
    }
}
