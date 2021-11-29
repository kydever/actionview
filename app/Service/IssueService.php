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
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;

class IssueService extends Service
{
    #[Inject]
    protected ProviderService $provider;

    public function index(Project $project, User $user)
    {
    }

    #[Cacheable(prefix: 'issue:options', value: '#{$project->id}', ttl: 86400, offset: 3600)]
    public function getOptions(Project $project)
    {
        $users = $this->provider->getUserList($project->key);

        return [
            'user' => $users,
        ];
    }
}
