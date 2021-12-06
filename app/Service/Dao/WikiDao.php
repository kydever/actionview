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
use App\Constants\ProjectConstant;
use App\Constants\StatusConstant;
use App\Exception\BusinessException;
use App\Model\Wiki;
use Han\Utils\Service;

class WikiDao extends Service
{
    public function exists(string $key, int $id): bool
    {
        return Wiki::query()
            ->where('project_key', $key)
            ->where('id', $id)
            ->where('d', ProjectConstant::WIKI_FOLDER)
            ->where('del_flag', '<>', StatusConstant::DELETED)
            ->exists();
    }

    public function existsNameInSameParent(string $key, int $parent, string $name): bool
    {
        return Wiki::query()
            ->where('project_key', $key)
            ->where('parent', $parent)
            ->where('name', $name)
            ->where('d', '<>', ProjectConstant::WIKI_FOLDER)
            ->where('del_flag', '<>', StatusConstant::DELETED)
            ->exists();
    }

    public function firstProjectKeyId(string $projectKey, int $id): ?Wiki
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->where('id', $id)
            ->first();
    }

    public function first(int $id, bool $throw = false): ?Wiki
    {
        $model = Wiki::find($id);
        if ($throw && empty($model)) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }
        return $model;
    }

    public function existsWidUser(int $wid, int $id): bool
    {
        return Wiki::query()
            ->where('wid', $wid)
            ->where('user->id', $id)
            ->exists();
    }

    public function firstVersion(string $projectKey, int $id, int $v): ?Wiki
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->where('wid', $id)
            ->where('version', $v)
            ->first();
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'contents' => '',
     * ],
     * @param mixed $directory
     * @param mixed $mode
     */
    public function search(array $input, string $projectKey, $directory, $mode)
    {
        $query = Wiki::query();

        if (! empty($projectKey)) {
            $query->where('project_key', $projectKey);
        }

        if (! empty($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        }

        if (! empty($input['contents'])) {
            $query->where('contents', 'like', '%' . $input['contents'] . '%');
        }

        if (! empty($input['updated_at'])) {
            $query->where(function ($query) use ($input) {
                $query->where('created_at', '>=', $input['updated_at']);
                $query->orWhere('updated_at', '>=', $input['updated_at']);
            });
        }

        //        if ($directory !== '0' && $mode === 'search') {
        //            $query->where('pt', $directory);
        //        }
        //
        //        if ($mode === 'list') {
        //            $query->where('parent', $directory);
        //        }

        $query->where('del_flag', '<>', StatusConstant::DELETED);

        $query->orderByDesc('d');
        $query->orderByDesc('id');
        return $query->get();
    }

    public function findMany(array $ids)
    {
        return Wiki::findManyFromCache($ids);
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Wiki[]
     */
    public function getName(string $projectKey, array $pt)
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->whereIn('id', $pt)
            ->get(['name']);
    }

    public function getParentWiki(string $projectKey, int $parent)
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->where('parent', $parent)
            ->where('del_flag', '<>', StatusConstant::DELETED)
            ->get();
    }

    public function existsUpdateInWikiName(string $projectKey, int $parent, string $name, $d)
    {
        $query = Wiki::query()
            ->where('project_key', $projectKey)
            ->where('parent', $parent)
            ->where('name', $name)
            ->where('del_flag', '<>', StatusConstant::DELETED);

        if (isset($d) && $d == 1) {
            $query->where('d', ProjectConstant::WIKI_FOLDER);
        } else {
            $query->where('d', '<>', ProjectConstant::WIKI_FOLDER);
        }

        return $query->exists();
    }

    public function searchPath($projectKey, $name, $movedPath)
    {
        $query = Wiki::query()
            ->where('project_key', $projectKey)
            ->where('name', 'like', '%' . $name . '%')
            ->where('d', ProjectConstant::WIKI_FOLDER)
            ->where('del_flag', '<>', StatusConstant::DELETED);

        //        现在 pt 是 JSON，_id 是 int 暂且注释
        //        if (isset($movedPath) && empty($movedPath)) {
        //            $query->where('pt', '<>', $movedPath);
        //            $query->where('_id', '<>', $movedPath);
        //        }

        $query->limit(20);
        return $query->get(['name', 'pt']);
    }

    public function firstProject($projectKey): ?Wiki
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->first();
    }



    public function updateDelFlag($projectKey, $id)
    {
        return Wiki::query()
            ->where('project_key', $projectKey)
            ->whereRaw("json_contains(pt,'{$id}')")
            ->update('del_flag', StatusConstant::DELETED);
    }
}
