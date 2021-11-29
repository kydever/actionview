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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Project;
use App\Service\Dao\ProjectDao;
use Hyperf\Utils\Traits\StaticInstance;

class ProjectAuth
{
    use StaticInstance;

    public ?Project $project = null;

    public function __construct(private ?string $projectKey = null)
    {
        if ($projectKey) {
            $this->project = di()->get(ProjectDao::class)->firstByKey($projectKey, true);
        }
    }

    public function isActive(): bool
    {
        return (bool) $this->getCurrent()?->isActive();
    }

    public function getCurrent(): ?Project
    {
        return $this->project;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }

    public function build()
    {
        if ($this->project === null) {
            throw new BusinessException(ErrorCode::PROJECT_NOT_EXIST);
        }

        return $this;
    }
}
