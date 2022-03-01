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
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Label;
use Han\Utils\Service;

class LabelDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|Label[]
     */
    public function getLabelOptions(string $key)
    {
        return Label::query()->where('project_key', $key)->orderBy('id')->get();
    }

    public function paginationByProjectKey(string $projectKey, int $offset = 0, int $limit = 10, array $columns = ['*']): array
    {
        $builder = Label::query()
            ->where('project_key', $projectKey)
            ->orderBy('id', 'desc');

        return $this->factory->model->pagination($builder, $offset, $limit, $columns);
    }

    public function findById(int $id): ?Label
    {
        return Label::findFromCache($id);
    }

    public function createOrUpdate(string $name, string $projectKey, ?string $bgColor, int $id = 0): bool
    {
        $model = $this->findById($id);
        if (empty($model)) {
            $model = new Label();
        }
        $model->name = $name;
        $model->project_key = $projectKey;
        $model->bgColor = $bgColor ?? '';

        return $model->save();
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);
        if (empty($model)) {
            throw new BusinessException(ErrorCode::LABEL_NOT_FOUND);
        }
        if (di(IssueDao::class)->count($model->project_key) > 0) {
            throw new BusinessException(ErrorCode::LABEL_USED_IESSUES);
        }

        return $model->delete();
    }
}
