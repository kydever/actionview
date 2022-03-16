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

use App\Service\Dao\ConfigResolutionDao;
use App\Service\Dao\ConfigResolutionPropertyDao;
use App\Service\Formatter\ConfigResolutionFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class ResolutionService extends Service
{
    #[Inject]
    protected ConfigResolutionFormatter $formatter;

    #[Inject]
    protected ConfigResolutionDao $dao;

    public function getResolutionList(string $projectKey): array
    {
        $models = $this->dao->findOrByProjectKey($projectKey);
        $lists = [];
        foreach ($models as $model) {
            $lists[] = $this->formatter->base($model);
        }
        $resolutionProperty = di()->get(ConfigResolutionPropertyDao::class)->firstByProjectKey($projectKey);
        if (! is_null($resolutionProperty)) {
            if ($sequence = $resolutionProperty->sequence) {
                $func = function ($v1, $v2) use ($sequence) {
                    $i1 = array_search($v1['id'], $sequence);
                    $i1 = $i1 !== false ? $i1 : 998;
                    $i2 = array_search($v2['id'], $sequence);
                    $i2 = $i2 !== false ? $i2 : 999;
                    return $i1 >= $i2 ? 1 : -1;
                };
                usort($lists, $func);
            }
            if ($defaultValue = $resolutionProperty->defaultValue) {
                foreach ($lists as $key => $list) {
                    if ($list['id'] == $defaultValue) {
                        $lists[$key]['default'] = true;
                    } elseif (isset($list['default'])) {
                        unset($lists[$key]['default']);
                    }
                }
            }
        }

        return $lists;
    }

    public function getResolutionOptions(string $projectKey): array
    {
        $resolutions = $this->getResolutionList($projectKey);
        $options = [];
        $tmp = [];
        foreach ($resolutions as $resolution) {
            $tmp['id'] = $resolution['key'] ?? $resolution['id'];
            $tmp['name'] = trim($resolution['name']) ?? '';
            if (isset($resolution['default'])) {
                $tmp['default'] = $resolution['default'];
            }
            $options[] = $tmp;
        }

        return $options;
    }
}
