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

use App\Model\Version;

class VersionEvent
{
    public function __construct(private Version $version, private array $extra = [])
    {
    }

    public function getVersion(): Version
    {
        return $this->version;
    }
}
