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

use App\Constants\ReportFiltersConstant;
use App\Service\Dao\ReportDao;
use App\Service\Dao\SprintDao;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\ReportFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ReportService extends Service
{
    #[Inject]
    protected ReportDao $dao;

    #[Inject]
    protected ReportFormatter $formatter;

    public function index()
    {
        $filters = ReportFiltersConstant::DEFAULT_REPORT_FILTERS;
        $models = $this->dao->getByProjectKey(get_project_key(), get_user_id());
        foreach ($models as $model) {
            if (isset($model['filters'])) {
                $filters[$model['mode']] = $model['filters'];
            }
        }
        foreach ($filters as $mode => $some_filters) {
            $filter[$mode] = $this->convFilters(get_project_key(), $some_filters);
        }

        return $filters;
    }

    public function convFilters(string $projectKey, array $filters)
    {
        foreach ($filters as $key => $filter) {
            if ($filter['id'] == 'active_sprint') {
                $sprint = di()->get(SprintDao::class)->firstByProjectKey($projectKey, 'active');
                if ($sprint) {
                    $filters[$key]['query'] = [
                        'sprints' => $sprint->no,
                    ];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'latest_completed_sprint') {
                $sprint = di()->get(SprintDao::class)->firstByProjectKey($projectKey, 'completed', 'desc');
                if ($sprint) {
                    $filters[$key]['query'] = [
                        'sprints' => $sprint->no,
                    ];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'will_release_version') {
                $version = di()->get(VersionDao::class)->firstByProjectKey(get_project_key(), 'unreleased');
                if ($version) {
                    $filters[$key]['query'] = ['resolve_version' => $version->id];
                } else {
                    unset($filters[$key]);
                }
            } elseif ($filter['id'] === 'latest_released_version') {
                $version = di()->get(VersionDao::class)->firstByProjectKey(get_project_key(), 'released');
                if ($version) {
                    $filters[$key]['query'] = ['resolve_version' => $version->id];
                } else {
                    unset($filters[$key]);
                }
            }
        }
        return array_values($filters);
    }
}
