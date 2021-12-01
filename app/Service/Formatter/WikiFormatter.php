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

use App\Model\Wiki;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class WikiFormatter extends Service
{
    public function base(Wiki $model)
    {
        return [
            'id' => $model->id,
//            'directory' => $model->directory ?: 'self',
//            'status' => $model->isInvalid() ? WikiConstant::INVALID : WikiConstant::ACTIVE,
        ];
    }

    /**
     * @param Wiki[] $models
     */
    public function formatList(Collection $models): array
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }

        return $result;
    }
}
