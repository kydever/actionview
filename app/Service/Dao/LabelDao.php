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
use Hyperf\Database\Model\Collection;

class LabelDao extends Service
{
    /**
     * @return Collection<int, Label>
     */
    public function getLabelOptions(string $key)
    {
        return Label::query()
            ->where('project_key', $key)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Label[]
     */
    public function findByName(string $key, array $names)
    {
        return Label::query()->where('project_key', $key)
            ->whereIn('name', $names)
            ->orderBy('id')
            ->get();
    }

    public function findById(int $id): ?Label
    {
        return Label::findFromCache($id);
    }

    public function existsByName(string $name): bool
    {
        return Label::where('name', $name)->exists();
    }

    public function createOrUpdate(int $id, string $projectKey, string $name, ?string $bgColor): bool
    {
        $model = $this->findById($id);
        $nameExists = $this->existsByName($name);
        if ($model && $nameExists && $model->name !== $name) {
            throw new BusinessException(ErrorCode::LABEL_NAME_ALREADY_EXISTED);
        }
        if (empty($model)) {
            if ($nameExists) {
                throw new BusinessException(ErrorCode::LABEL_NAME_ALREADY_EXISTED);
            }
            $model = new Label();
        }
        $model->project_key = $projectKey;
        $model->name = $name;
        $model->bgColor = $bgColor ?? '';

        return $model->save();
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);
        if (empty($model)) {
            throw new BusinessException(ErrorCode::LABEL_NOT_FOUND);
        }
        if (di(IssueDao::class)->exists($model->project_key, $model->name)) {
            throw new BusinessException(ErrorCode::LABEL_USED_IESSUES);
        }

        return $model->delete();
    }
}
