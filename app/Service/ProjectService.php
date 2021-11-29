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
use App\Constants\Permission;
use App\Constants\ProjectConstant;
use App\Constants\StatusConstant;
use App\Event\AddUserToRoleEvent;
use App\Event\DelUserFromRoleEvent;
use App\Exception\BusinessException;
use App\Model\ConfigType;
use App\Model\Project;
use App\Model\User;
use App\Service\Dao\AccessProjectLogDao;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\ActivityDao;
use App\Service\Dao\ConfigTypeDao;
use App\Service\Dao\IssueDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\SysSettingDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserGroupProjectDao;
use App\Service\Formatter\ProjectFormatter;
use App\Service\Struct\Principal;
use Carbon\Carbon;
use Han\Utils\Service;
use Han\Utils\Utils\Sorter;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;

class ProjectService extends Service
{
    #[Inject]
    protected ProjectDao $dao;

    #[Inject]
    protected AclService $acl;

    #[Inject]
    protected IssueDao $issue;

    #[Inject]
    protected ActivityDao $activity;

    #[Inject]
    protected ProjectFormatter $formatter;

    public function getLatestAccessProject(int $userId): ?Project
    {
        $model = di()->get(AccessProjectLogDao::class)->latest($userId);
        if ($model?->project?->isActive()) {
            return $model->project;
        }

        return null;
    }

    /**
     * 根据项目ID和标记KEY获取对应的issue等数量.
     * @param mixed $userId
     * @return array<string, int> 项目KEY => 数量
     */
    public function getOpenCount(array $keys, string $sortkey, $userId = 0): ?array
    {
        return match ($sortkey) {
            ProjectConstant::SORT_KEY_ALL_ISSUES_CNT => $this->issue->countGroupByProjectKeys($keys),
            ProjectConstant::SORT_KEY_UNRESOLVED_ISSUES_CNT => $this->issue->countGroupByProjectKeys($keys, 'Unresolved'),
            ProjectConstant::SORT_KEY_ASSIGNTOME_ISSUES_CNT => $this->issue->countGroupByProjectKeys($keys, 'Unresolved', $userId),
            ProjectConstant::SORT_KEY_ACTIVITY => $this->activity->recentCountGroupByProjectKeys($keys, 14),
            default => null,
        };
    }

    #[Cacheable(prefix: 'project:all', ttl: 8640000)]
    public function getAllProjectKeys()
    {
        return $this->dao->findAllProjectKeys();
    }

    #[CachePut(prefix: 'project:all', ttl: 8640000)]
    public function putAllProjectKeys()
    {
        return $this->dao->findAllProjectKeys();
    }

    public function sortByCreatedAt(array $keys, string $sort)
    {
        $projectKeys = $this->getAllProjectKeys();
        return di()->get(Sorter::class)->sort($keys, static function ($key) use ($projectKeys, $sort) {
            $priority = $projectKeys[$key] ?? 0;

            return match ($sort) {
                StatusConstant::ASC => PHP_INT_MAX - $priority,
                default => $priority,
            };
        });
    }

    public function mine(int $userId, array $input = [], )
    {
        $sortKey = $input['sortkey'] ?? null;
        $offsetKey = $input['offset_key'] ?? null;
        $limit = intval($input['limit'] ?? 24);
        $status = $input['status'] ?? 'all';
        $name = $input['name'] ?? null;

        $keys = $this->getRecentProjectKeys($userId);

        if ($sortKey) {
            $openCount = $this->getOpenCount($keys, $sortKey, $userId);
            if ($openCount !== null) {
                arsort($openCount);
                $keys = array_keys($openCount);
            } else {
                $keys = match ($sortKey) {
                    ProjectConstant::SORT_KEY_KEY_ASC => value(
                        static function () use ($keys) {
                            sort($keys);
                            return $keys;
                        }
                    ),
                    ProjectConstant::SORT_KEY_KEY_DESC => value(
                        static function () use ($keys) {
                            rsort($keys);
                            return $keys;
                        }
                    ),
                    ProjectConstant::SORT_KEY_CREATE_TIME_ASC => $this->sortByCreatedAt($keys, StatusConstant::ASC),
                    ProjectConstant::SORT_KEY_CREATE_TIME_DESC => $this->sortByCreatedAt($keys, StatusConstant::DESC),
                    default => [],
                };
            }
        }

        if (isset($offsetKey)) {
            $ind = array_search($offsetKey, $keys);
            if ($ind === false) {
                $keys = [];
            } else {
                $keys = array_slice($keys, $ind + 1);
            }
        }

        $projects = $this->dao->find([
            'keys' => $keys,
            'key_or_name' => $name,
            'status' => $status,
        ], $limit);

        $result = $this->formatter->formatList($projects);
        $setting = di()->get(SysSettingDao::class)->first();
        return [
            $result,
            [
                'limit' => $limit,
                'allow_create_project' => $setting->properties['allow_create_project'] ?? 0,
            ],
        ];
    }

    public function recent(int $userId)
    {
        $keys = $this->getRecentProjectKeys($userId);
        $projects = di()->get(ProjectDao::class)->findByKeys($keys);
        $result = [];
        foreach ($projects as $project) {
            if (! $project->isActive()) {
                continue;
            }

            $result[] = $this->formatter->small($project);

            if (count($result) >= 5) {
                break;
            }
        }

        return $result;
    }

    public function getRecentProjectKeys(int $userId): array
    {
        $groupIds = di()->get(AclGroupDao::class)->findByUserId($userId)->columns('id')->toArray();
        $projectKeys = di()->get(UserGroupProjectDao::class)->findByUGIds([$userId, ...$groupIds])->columns('project_key')->toArray();
        $accessedProjectKeys = di()->get(AccessProjectLogDao::class)->findLatestProjectKeys($userId);

        return array_values(array_unique(array_intersect($projectKeys, $accessedProjectKeys)));
    }

    /**
     * @param $input = [
     *     'name' => 'required',
     *     'description' => 'required',
     *     'principal' => 'required',
     *     'status' => 'active',
     * ]
     */
    public function update(int $id, array $input, User $user)
    {
        $name = $input['name'] ?? null;
        $principal = new Principal($input['principal'] ?? null);
        $description = $input['description'] ?? null;
        $status = $input['status'] ?? null;

        $model = $this->dao->first($id, true);
        if (! $model->isPrincipal($user->id) && ! $user->hasAccess(Permission::SYS_ADMIN)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $principalId = $model->getPrincipal()['id'] ?? null;

        isset($name) && $model->name = $name;
        $principal->isChanged() && $model->principal = $principal->toArray();
        isset($description) && $model->description = $description;
        isset($status) && $model->status = $status;

        Db::beginTransaction();
        try {
            $model->save();

            if ($principalId != $principal->getPrincipal()) {
                di()->get(EventDispatcherInterface::class)->dispatch(new AddUserToRoleEvent([$principal->getPrincipal()], $model->key));
                di()->get(EventDispatcherInterface::class)->dispatch(new DelUserFromRoleEvent([$principalId], $model->key));
            }
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->showProject($model, $user->id);
    }

    /**
     * @param $input = [
     *     'key' => 'required',
     *     'name' => 'required',
     *     'description' => 'required',
     *     'principal' => 'required',
     * ]
     */
    public function store(int $userId, array $input)
    {
        $user = di()->get(UserDao::class)->first($userId, true);
        $setting = di()->get(SysSettingDao::class)->first();
        if (! $setting->allowCreateProject() && ! $user->hasAccess(Permission::SYS_ADMIN)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $name = $input['name'];
        $key = ProjectConstant::formatProjectKey($input['key']);
        $principalId = (string) ($input['principal'] ?? null);
        $description = $input['description'] ?? '';
        $creator = [
            'id' => $user->id,
            'name' => $user->first_name,
            'email' => $user->email,
        ];

        if ($this->dao->exists($key)) {
            throw new BusinessException(ErrorCode::PROJECT_KEY_HAS_BEEN_TAKEN);
        }

        $principal = new Principal($principalId, $user);

        Db::beginTransaction();
        try {
            $project = $this->dao->create($key, $name, $description, $creator, $principal->toArray());

            $this->initialize($project->key);

            di()->get(EventDispatcherInterface::class)->dispatch(new AddUserToRoleEvent([$project->getPrincipal()['id']], $project->key));
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();

            throw $exception;
        }

        $this->putAllProjectKeys();

        return $this->formatter->base($project);
    }

    /**
     * 初始化项目相关数据.
     */
    public function initialize(string $key)
    {
        $default = di()->get(ConfigTypeDao::class)->findDefault();
        $values = [];
        $now = Carbon::now()->toDateTimeString();
        foreach ($default as $item) {
            $values[] = [
                'name' => $item->name,
                'abb' => $item->abb,
                'screen_id' => $item->screen_id,
                'workflow_id' => $item->workflow_id,
                'sn' => $item->sn,
                'type' => $item->type,
                'disabled' => $item->disabled,
                'default' => $item->default,
                'project_key' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ConfigType::query()->insert($values);
    }

    public function index(array $input, int $offset, int $limit)
    {
        $input['key_or_name'] = $input['name'] ?? null;

        [$count, $projects] = $this->dao->search($input, $offset, $limit);

        $result = $this->formatter->formatList($projects);

        return [$count, $result];
    }

    public function show(string $key, int $userId)
    {
        $model = $this->dao->firstByKey($key, true);

        return $this->showProject($model, $userId)
    }

    protected function showProject(Project $model, int $userId){
        $permissions = di()->get(AclService::class)->getPermissions($userId, $model);

        // record the project access date
        if (in_array('view_project', $permissions) && $model->isActive()) {
            di()->get(AccessProjectLogDao::class)->create(
                $model->key,
                $userId
            );
        }

        return [$this->formatter->base($model), $permissions];
    }
}
