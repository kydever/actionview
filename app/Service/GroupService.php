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
use App\Constants\StatusConstant;
use App\Event\DeleteGroupEvent;
use App\Exception\BusinessException;
use App\Model\AclGroup;
use App\Model\User;
use App\Service\Dao\AclGroupDao;
use App\Service\Formatter\GroupFormatter;
use App\Service\Struct\Principal;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class GroupService extends Service
{
    #[Inject]
    protected AclGroupDao $dao;

    #[Inject]
    protected GroupFormatter $formatter;

    public function index(array $input, int $offset, int $limit)
    {
        [$total, $models] = $this->dao->find($input, $offset, $limit);

        $models->load('userModels');

        $result = $this->formatter->formatList($models);

        return [$total, $result];
    }

    public function search(string $keyword, int $userId)
    {
        if (empty($keyword)) {
            return [];
        }

        $models = $this->dao->search($keyword, $userId);

        return $models->columns(['id', 'name'])->toArray();
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'principal' => 'self',
     *     'public_scope' => '1',
     *     'description' => '',
     *     'source_id' => 1,
     *     'users' => [],
     * ]
     */
    public function store(int $id, array $input, User $user)
    {
        $name = $input['name'] ?? null;
        $principal = $input['principal'] ?? null;
        $scope = $input['public_scope'] ?? null;
        $description = $input['description'] ?? null;
        $sourceId = $input['source_id'] ?? null;
        $users = $input['users'] ?? null;

        $principal = new Principal($principal, $user);

        if ($sourceId) {
            $group = $this->dao->first($sourceId, true);
            $users = $group->users ?? [];
        }

        if ($id > 0) {
            $model = $this->dao->first($id, true);
            if ($model->directory && ! $model->isSelfDirectory()) {
                throw new BusinessException(ErrorCode::GROUP_FROM_EXTERNAL_DIRECTION);
            }

            if (! $model->isPrincipal($user->id) && ! $user->hasAccess(Permission::SYS_ADMIN)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
        } else {
            $model = new AclGroup();
            $scope = $scope ?? StatusConstant::SCOPE_PUBLIC;
            $description = $description ?? '';
            $users = $users ?? [];
        }

        isset($name) && $model->name = $name;
        $principal->isChanged() && $model->principal = $principal->toArray();
        isset($scope) && $model->public_scope = $scope;
        isset($description) && $model->description = $description;
        isset($users) && $model->users = $users;
        $model->save();

        return $this->formatter->detail($model);
    }

    #[Cacheable(prefix: 'group:all', ttl: 8640000)]
    public function getAll(): array
    {
        return $this->all();
    }

    #[CachePut(prefix: 'group:all', ttl: 8640000)]
    public function putAll(): array
    {
        return $this->all();
    }

    public function all(): array
    {
        $models = $this->dao->all();

        return $this->formatter->formatList($models);
    }

    public function destroy(int $id, User $user): int
    {
        $model = $this->dao->first($id, true);
        if ($model->directory && $model->isSelfDirectory()) {
            throw new BusinessException(ErrorCode::GROUP_FROM_EXTERNAL_DIRECTION);
        }

        if (! $model->isPrincipal($user->id) && ! $user->hasAccess(Permission::SYS_ADMIN)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        Db::beginTransaction();
        try {
            $model->delete();
            di()->get(EventDispatcherInterface::class)->dispatch(new DeleteGroupEvent($id));
            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();

            throw $exception;
        }

        return $id;
    }

    public function mygroup(array $input, int $userId, int $offset, int $limit)
    {
        if ($scale = $input['scale'] ?? null) {
            $input['scale'] = match ($scale) {
                'myprincipal' => ['myprincipal', $userId],
                'myjoin' => ['myjoin', $userId],
                default => ['', $userId],
            };
        }

        [$total, $models] = $this->dao->find($input, $offset, $limit);

        $models->load('userModels');

        $result = $this->formatter->formatList($models);

        return [$total, $result];
    }
}
