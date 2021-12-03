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
use App\Constants\StatusConstant;
use App\Event\VersionEvent;
use App\Exception\BusinessException;
use App\Model\Project;
use App\Model\User;
use App\Model\Version;
use App\Service\Client\IssueSearch;
use App\Service\Dao\VersionDao;
use App\Service\Formatter\UserFormatter;
use App\Service\Formatter\VersionFormatter;
use Han\Utils\Service;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Psr\EventDispatcher\EventDispatcherInterface;

class VersionService extends Service
{
    #[Inject]
    protected VersionDao $dao;

    #[Inject]
    protected VersionFormatter $formatter;

    #[Inject]
    protected ProviderService $provider;

    public function update(int $id, array $input, User $user, Project $project)
    {
        $version = $this->dao->first($id, true);
        $name = $input['name'] ?? null;
        $startTime = $input['start_time'] ?? $version->start_time;
        $endTime = $input['end_time'] ?? $version->end_time;
        $description = $input['description'] ?? null;
        $status = $input['status'] ?? null;

        if ($startTime > $endTime) {
            throw new BusinessException(ErrorCode::VERSION_END_TIME_MUST_LARGER_THAN_START_TIME);
        }

        if ($name !== null) {
            if (empty($name)) {
                throw new BusinessException(ErrorCode::VERSION_NAME_CANNOT_EMPTY);
            }

            if ($version->name !== $name && $this->dao->firstByName($project->key, $name)) {
                throw new BusinessException(ErrorCode::VERSION_NAME_REPEATED);
            }
        }

        if (! Arr::only($input, ['name', 'start_time', 'end_time', 'description', 'status'])) {
            return $this->show($version);
        }

        isset($name) && $version->name = $name;
        isset($startTime) && $version->start_time = $startTime;
        isset($endTime) && $version->end_time = $endTime;
        isset($description) && $version->description = $description;
        isset($status) && $version->status = $status;
        $version->modifier = di()->get(UserFormatter::class)->small($user);

        Db::beginTransaction();
        try {
            $version->save();
            di()->get(EventDispatcherInterface::class)->dispatch(new VersionEvent($version));
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($version);
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'start_time' => 123,
     *     'end_time' => 123,
     * ]
     */
    public function store(array $input, User $user, Project $project)
    {
        $name = $input['name'];
        $startTime = $input['start_time'] ?? null;
        $endTime = $input['end_time'] ?? null;
        $description = $input['description'] ?? '';
        if ($this->dao->firstByName($project->key, $name)) {
            throw new BusinessException(ErrorCode::VERSION_NAME_REPEATED);
        }

        if ($startTime && $endTime && $startTime > $endTime) {
            throw new BusinessException(ErrorCode::VERSION_END_TIME_MUST_LARGER_THAN_START_TIME);
        }

        $creator = di()->get(UserFormatter::class)->small($user);
        $model = new Version();
        $model->project_key = $project->key;
        $model->name = $name;
        $model->creator = $creator;
        $model->status = StatusConstant::STATUS_UNRELEASED;
        $model->description = $description;

        Db::beginTransaction();
        try {
            $model->save();
            di()->get(EventDispatcherInterface::class)->dispatch(new VersionEvent($model));
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($model);
    }

    public function show(Version $version)
    {
        $result = $this->formatter->base($version);
        $result['is_used'] = false;

        $res = di()->get(IssueSearch::class)->countByVersion([$version->id]);
        $result['all_cnt'] = $res[$version->id]['cnt'] ?? 0;
        $result['unresolved_cnt'] = $res[$version->id]['unresolved_cnt'] ?? 0;
        return $result;
    }

    public function index(Project $project, int $offset, int $limit): array
    {
        [$count, $models] = $this->dao->index($project->key, $offset, $limit);

        // TODO: 从搜索引擎中查询对应数量
        // $versionFieldModels = $this->provider->getFieldList($project->key);

        $versionIds = $models->columns('id')->toArray();
        $res = di()->get(IssueSearch::class)->countByVersion($versionIds);

        $result = $this->formatter->formatList($models, $res);

        $options = ['total' => $count, 'sizePerPage' => $limit, 'current_time' => time()];

        return [$result, $options];
    }
}
