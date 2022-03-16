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

use App\Service\Dao\ConfigPriorityDao;
use App\Service\Dao\ConfigPriorityPropertyDao;
use App\Service\Formatter\ConfigPriorityFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class PriorityService extends Service
{
    #[Inject]
    protected ConfigPriorityDao $dao;

    #[Inject]
    protected ConfigPriorityFormatter $formatter;

    public function getPriorityList(string $projectKey, array $columns = ['*']): array
    {
        $models = $this->dao->findOrByProjectKey($projectKey);
        $lists = [];
        foreach ($models as $model) {
            $lists[] = $this->formatter->base($model);
        }

        $priorityProperty = di()->get(ConfigPriorityPropertyDao::class)->firstByProjectKey($projectKey);
        if (! is_null($priorityProperty)) {
            if ($sequence = $priorityProperty->sequence) {
                $func = function ($v1, $v2) use ($sequence) {
                    $i1 = array_search($v1['id'], $sequence);
                    $i1 = $i1 !== false ? $i1 : 998;
                    $i2 = array_search($v2['id'], $sequence);
                    $i2 = $i2 !== false ? $i2 : 999;
                    return $i1 >= $i2 ? 1 : -1;
                };
                usort($lists, $func);
            }
            if ($defaultValue = $priorityProperty->defaultValue) {
                foreach ($lists as $key => $val) {
                    if ($val['id'] == $defaultValue) {
                        $lists[$key]['default'] = true;
                    } elseif (isset($val['default'])) {
                        unset($lists[$key]['default']);
                    }
                }
            }
        }

        return $lists;
    }

    public function getPriorityOptions(string $projectKey): array
    {
        $priorities = $this->getPriorityList($projectKey);
        $options = [];
        $tmp = [];
        foreach ($priorities as $priority) {
            $tmp['id'] = isset($priority['key']) && $priority['key'] ? $priority['key'] : $priority['id'];
            $tmp['name'] = isset($priority['name']) ? trim($priority['name']) : '';
            if (isset($priority['default'])) {
                $tmp['default'] = $priority['default'];
            }
            if (isset($priority['color'])) {
                $tmp['color'] = $priority['color'];
            }
            $options[] = $tmp;
        }

        return $options;
    }
}
