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

use App\Service\Dao\SysSettingDao;

trait TimeTrackTrait
{
    /**
     * check the timetracking.
     *
     * @param mixed $ttString
     * @return bool
     */
    public function ttCheck($ttString)
    {
        $ttString = strtolower(trim($ttString));
        $ttValues = explode(' ', $ttString);
        foreach ($ttValues as $ttValue) {
            if (! $ttValue) {
                continue;
            }
            $lastChr = substr($ttValue, -1);
            if ($lastChr !== 'w' && $lastChr !== 'd' && $lastChr !== 'h' && $lastChr !== 'm') {
                return false;
            }
            $ttNum = substr($ttValue, 0, -1);
            if ($ttNum && ! is_numeric($ttNum)) {
                return false;
            }
        }
        return true;
    }

    /**
     * handle the timetracking in the minute.
     *
     * @param mixed $ttString
     * @return string
     */
    public function ttHandleInM($ttString)
    {
        if (! $ttString) {
            return '';
        }
        $W2D = 5;
        $D2H = 8;
        $setting = di()->get(SysSettingDao::class)->first();
        if ($setting && isset($setting->properties)) {
            if (isset($setting->properties['week2day'])) {
                $W2D = $setting->properties['week2day'];
            }
            if (isset($setting->properties['day2hour'])) {
                $D2H = $setting->properties['day2hour'];
            }
        }
        $W2M = $W2D * $D2H * 60;
        $D2M = $D2H * 60;
        $H2M = 60;
        $tt_in_min = 0;
        $ttString = strtolower(trim($ttString));
        $ttValues = explode(' ', $ttString);
        foreach ($ttValues as $ttValue) {
            if (! $ttValue) {
                continue;
            }
            $lastChr = substr($ttValue, -1);
            $ttNum = substr($ttValue, 0, -1) === '' ? 1 : substr($ttValue, 0, -1);
            if ($lastChr == 'w') {
                $tt_in_min += $ttNum * $W2M;
            } elseif ($lastChr == 'd') {
                $tt_in_min += $ttNum * $D2M;
            } elseif ($lastChr == 'h') {
                $tt_in_min += $ttNum * $H2M;
            } elseif ($lastChr == 'm') {
                $tt_in_min += $ttNum;
            }
        }
        return $tt_in_min;
    }

    /**
     * handle the timetracking.
     *
     * @param mixed $ttString
     * @return string
     */
    public function ttHandle($ttString)
    {
        if (! $ttString) {
            return '';
        }

        $W2D = 5;
        $D2H = 8;
        $setting = di()->get(SysSettingDao::class)->first();
        if ($setting && isset($setting->properties)) {
            if (isset($setting->properties['week2day'])) {
                $W2D = $setting->properties['week2day'];
            }
            if (isset($setting->properties['day2hour'])) {
                $D2H = $setting->properties['day2hour'];
            }
        }
        $W2M = $W2D * $D2H * 60;
        $D2M = $D2H * 60;
        $H2M = 60;
        $tt_in_min = 0;
        $ttString = strtolower(trim($ttString));
        $ttValues = explode(' ', $ttString);
        foreach ($ttValues as $ttValue) {
            if (! $ttValue) {
                continue;
            }
            $lastChr = substr($ttValue, -1);
            $ttNum = substr($ttValue, 0, -1) === '' ? 1 : abs(substr($ttValue, 0, -1));
            if ($lastChr == 'w') {
                $tt_in_min += $ttNum * $W2M;
            } elseif ($lastChr == 'd') {
                $tt_in_min += $ttNum * $D2M;
            } elseif ($lastChr == 'h') {
                $tt_in_min += $ttNum * $H2M;
            } elseif ($lastChr == 'm') {
                $tt_in_min += $ttNum;
            }
        }
        $newTT = [];
        $new_remain_min = ceil($tt_in_min);
        if ($new_remain_min >= 0) {
            $new_weeknum = floor($tt_in_min / $W2M);
            if ($new_weeknum > 0) {
                $newTT[] = $new_weeknum . 'w';
            }
        }
        $new_remain_min = $tt_in_min % $W2M;
        if ($new_remain_min >= 0) {
            $new_daynum = floor($new_remain_min / $D2M);
            if ($new_daynum > 0) {
                $newTT[] = $new_daynum . 'd';
            }
        }
        $new_remain_min = $new_remain_min % $D2M;
        if ($new_remain_min >= 0) {
            $new_hournum = floor($new_remain_min / $H2M);
            if ($new_hournum > 0) {
                $newTT[] = $new_hournum . 'h';
            }
        }
        $new_remain_min = $new_remain_min % $H2M;
        if ($new_remain_min > 0) {
            $newTT[] = $new_remain_min . 'm';
        }
        if (! $newTT) {
            $newTT[] = '0m';
        }
        return (str_starts_with($ttString, '-') ? '-' : '') . implode(' ', $newTT);
    }

    public function isTimestamp($timestamp)
    {
        if (strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        }

        return false;
    }
}
