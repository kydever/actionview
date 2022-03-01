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
namespace App\Event;

use App\Model\Label;

class LabelEvent
{
    public function __construct(protected Label $label)
    {
    }

    public function getLabel(): Label
    {
        return $this->label;
    }
}
