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

use App\Model\Project;
use App\Model\User;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Formatter\ConfigTypeFormatter;
use Carbon\Carbon;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ProjectSummaryService extends Service
{
    #[Inject]
    protected ProviderService $provider;

    public function index(Project $project, User $user)
    {
        // the top four filters
        $filters = $this->getTopFourFilters($project, $user);
        $trend = $this->getPulseData($project);
        $types = di()->get(ConfigTypeFormatter::class)->formatList(
            di()->get(ConfigTypeDao::class)->getTypeList($project->key)
        );

        return [
            [
                'filters' => $filters,
                'trend' => $trend,
            ],
            [
                'types' => $types,
                'twoWeeksAgo' => Carbon::now()->subWeeks(2)->format('m/d'),
            ],
        ];
    }

    /**
     * get the top four filters info.
     */
    public function getTopFourFilters(Project $project, User $user)
    {
        $filters = $this->provider->getIssueFilters($project->key, $user->id);
        $filters = array_slice($filters, 0, 4);
        foreach ($filters as $key => $filter) {
            $query = [];
            if (isset($filter['query']) && $filter['query']) {
                $query = $filter['query'];
            }

            $count = di()->get(IssueSearch::class)->countByBoolQuery(
                di()->get(IssueService::class)->getBoolSearch($project->key, $query, $user->id)
            );

            $filters[$key]['count'] = $count;
        }

        return $filters;
    }

    /**
     * get the past two weeks trend data.
     */
    public function getPulseData(Project $project)
    {
        // initialize the results
        $trend = $this->init14DaysArray();

        $countDaily = di()->get(IssueSearch::class)->countDaily($project->key);
        foreach ($countDaily as $date => $item) {
            $trend[$date] = [
                'new' => $item['created_cnt'] ?? 0,
                'resolved' => $item['resolved_cnt'] ?? 0,
                'closed' => $item['closed_cnt'] ?? 0,
            ];
        }

        $result = [];
        ksort($trend);
        foreach ($trend as $key => $val) {
            $result[] = ['day' => $key] + $val;
        }
        return $result;
    }

    private function init14DaysArray(): array
    {
        $now = Carbon::today();
        $result = [];
        for ($i = 0; $i < 14; ++$i) {
            $result[$now->format('Y/m/d')] = ['new' => 0, 'resolved' => 0, 'closed' => 0];
        }
        return $result;
    }
}
