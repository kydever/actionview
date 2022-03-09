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

use App\Model\ConfigType;
use App\Service\StateService;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class TypeFormatter extends Service
{
    #[Inject]
    protected ProviderService $provider;

    public function base(ConfigType $model)
    {
        return [
            'id' => $model->id,
            'sn' => $model->sn,
            'name' => $model->name,
            'abb' => $model->abb,
            'type' => $model->type,
            'default' => $model->default,
            'description' => $model->description,
            'disabled' => $model->disabled,
        ];
    }

    /**
     * @param Collection<int, ConfigType> $models
     */
    public function formatList(Collection $models, bool $withIsUsed = false)
    {
        $result = [];
        foreach ($models as $model) {
            $item = $this->base($model);
            $item['is_used'] = di()->get(StateService::class)->isFieldUsedByIssue($model->project_key);
        }
        $results = [];
        $stateService = di()->get(StateService::class);
        foreach ($models as $model) {
            $model->is_used = $stateService->isFieldUsedByIssue($project, 'type', $model->toArray());
        }

        return $results;
    }
}
