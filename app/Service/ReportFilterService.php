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

use App\Model\ReportFilter;
use App\Service\Dao\ReportFilterDao;
use App\Service\Formatter\ReportFilterFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ReportFilterService extends Service
{
    #[Inject]
    protected ReportFilterDao $dao;

    #[Inject]
    protected ReportFilterFormatter $formatter;

    public function create(array $attributes): array
    {
        $model = new ReportFilter();
        $model->user_id = $attributes['userId'];
        $model->project_key = $attributes['projectKey'];
        $model->mode = $attributes['mode'];
        $model->filters = [
            'id' => md5(microtime()),
            'name' => $attributes['name'],
            'query' => $attributes['query'],
        ];
        $model->save();

        return $this->formatter->base($model);
    }
}
