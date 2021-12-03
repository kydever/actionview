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
use App\Service\Dao\VersionDao;
use App\Service\Formatter\UserFormatter;
use App\Service\Formatter\VersionFormatter;
use Han\Utils\Service;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;

class VersionService extends Service
{
    #[Inject]
    protected VersionDao $dao;

    #[Inject]
    protected VersionFormatter $formatter;

    #[Inject]
    protected ProviderService $provider;

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

        return $this->formatter->base($model);
    }

    public function index(Project $project, int $offset, int $limit): array
    {
        [$count, $models] = $this->dao->index($project->key, $offset, $limit);

        // TODO: 从搜索引擎中查询对应数量
        // $versionIds = $models->columns('id')->toArray();
        // $versionFieldModels = $this->provider->getFieldList($project->key);

        $result = $this->formatter->formatList($models);

        $options = ['total' => $count, 'sizePerPage' => $limit, 'current_time' => time()];

        return [$result, $options];
    }
}
