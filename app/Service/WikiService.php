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
use App\Exception\BusinessException;
use App\Model\Project;
use App\Model\User;
use App\Model\Wiki;
use App\Service\Dao\WikiDao;
use App\Service\Dao\WikiFavoriteDao;
use App\Service\Formatter\UserFormatter;
use App\Service\Formatter\WikiFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;

class WikiService extends Service
{
    #[Inject]
    protected WikiDao $dao;

    #[Inject]
    protected WikiFormatter $formatter;

    public function createDoc(array $input, User $user, Project $project)
    {
        $parentId = (int) ($input['parent'] ?? null);
        $name = $input['name'] ?? '';
        $contents = $input['contents'] ?? '';

        $parent = $this->dao->first($parentId, true);

        if (empty($name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        if ($this->dao->existsNameInSameParent($project->key, $parent->id, $name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $model = new Wiki();
        $model->project_key = $project->key;
        $model->parent = $parent->id;
        $model->d = ProjectConstant::WIKI_CONTENTS;
        $model->del_flag = StatusConstant::NOT_DELETED;
        $model->name = $name;
        $model->pt = $this->getPathTree($parent);
        $model->version = 1;
        $model->creator = di()->get(UserFormatter::class)->base($user);
        $model->editor = [];
        $model->contents = $contents;
        $model->save();

        //         TODO 需要需改
        //        $isSendMsg = $input['isSendMsg'] && true;
        //        Event::fire(new WikiEvent($projectKey, $insValues['creator'], ['event_key' => 'create_wiki', 'isSendMsg' => $isSendMsg, 'data' => ['wiki_id' => $id->__toString()]]));
        return $this->show($input, $model, $user);
    }

    public function getPathTree(?Wiki $parent): array
    {
        if ($parent === null) {
            return [0];
        }

        return array_merge($parent->pt, [$parent->id]);
    }

    public function show($input, Wiki $model, User $user)
    {
        $favorited = false;
        if (di()->get(WikiFavoriteDao::class)->first($model->id, $user->id)) {
            $favorited = true;
        }

        // $newest = [];
        // $newest['name'] = $model->name;
        // $newest['editor'] = $model->editor ?: $model->creator;
        // $newest['updated_at'] = $model->updated_at->toDateTimeString();
        // $newest['version'] = $model->version;

        //         TODO 先注释后续再看
        //        $v = $input['v'] ?? '';
        //
        //        if (isset($v) && intval($v) != $document['version']) {
        //            // 传过来的版本如果和数据表存储的不相等
        //            $wiki = $this->dao->firstVersion($input['project_key'], $id, $v);
        //            if ($wiki) {
        //                throw new \UnexpectedValueException('the version does not exist.', -11957);
        //            }
        //            $document['name'] = $wiki['name'];
        //            $document['contents'] = $wiki['contents'];
        //            $document['editor'] = $wiki['editor'];
        //            $document['updated_at'] = $wiki['updated_at'];
        //            $document['version'] = $wiki['version'];
        //        }

        // $document['versions'] = $this->dao->search($input, $id);
        //
        // array_unshift($newest, $document['versions']);

        $path = $this->getPathTreeDetail($model->pt);
        $result = $this->formatter->base($model);
        $result['favorited'] = $favorited;

        return [$result, $path];
    }

    public function getPathTreeDetail(array $pt)
    {
        $parents = $this->dao->findMany($pt)->getDictionary();

        $path = [];
        foreach ($pt as $pid) {
            if ($pid === 0) {
                $path[] = ['id' => 0, 'name' => 'root'];
            } elseif (isset($parents[$pid]) && $parents[$pid] instanceof Wiki) {
                $path[] = ['id' => $pid, 'name' => $parents[$pid]->name];
            }
        }
        return $path;
    }

    public function createFolder(array $input, User $user, Project $project)
    {
        $parentId = $input['parent'] ?? null;
        if (! isset($parentId)) {
            throw new BusinessException(ErrorCode::PARENT_NOT_EMPTY);
        }
        $name = $input['name'] ?? '';
        if (! $name) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        $parentId = (int) $parentId;
        $parent = null;
        if ($parentId > 0) {
            $parent = $this->dao->first($parentId, true);
        }

        $isExists = $this->dao->existsNameInSameParent($project->key, $parent?->id ?? 0, $name);
        if ($isExists) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $model = new Wiki();
        $model->project_key = $project->key;
        $model->parent = $parentId;
        $model->name = $name;
        $model->pt = $this->getPathTree($parent);
        $model->d = ProjectConstant::WIKI_FOLDER;
        $model->creator = di()->get(UserFormatter::class)->small($user);
        $model->editor = [];
        $model->del_flag = StatusConstant::NOT_DELETED;
        $model->save();

        return $this->formatter->base($model);
    }

    public function getDirTree($curNode, $dt, Project $project)
    {
        $pt = ['0'];
        if (empty($curNode)) {
            if ($curNode == 'root') {
                $curNode = null;
            }
            $node = $this->dao->firstParent($project->key, (int) $curNode);
            if ($node) {
                $pt = $node->pt;
                if (isset($node->d) && $node->d == 1) {
                    array_push($pt, $curNode);
                }
            }
        }

        foreach ($pt as $val) {
            $subDirs = $this->dao->getParentWiki($project->key, (int) $val);
            $this->addChildren2Tree($dt, $val, $subDirs);
        }

        return $dt;
    }

    public function addChildren2Tree(&$dt, $parent_id, $sub_dirs)
    {
        $new_dirs = [];
        foreach ($sub_dirs as $val) {
            $new_dirs[] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'd' => isset($val['d']) ? $val['d'] : 0,
                'parent' => (string) (isset($val['parent']) ? $val['parent'] : ''),
            ];
        }

        if ($dt['id'] == $parent_id) {
            $dt['children'] = $new_dirs;
            return true;
        }

        if (isset($dt['children']) && $dt['children']) {
            $children_num = count($dt['children']);
            for ($i = 0; $i < $children_num; ++$i) {
                $res = $this->addChildren2Tree($dt['children'][$i], $parent_id, $sub_dirs);
                if ($res === true) {
                    return true;
                }
            }
        }
        return false;
    }

    public function index(array $input, int $directory, Project $project)
    {
        $mode = 'list';
        if (isset($input['name']) || isset($input['contents']) || isset($input['updated_at'])) {
            $mode = 'search';
        }

        $documents = $this->dao->search($input, $project->key, $directory, $mode) ?? '';
        $documents = $this->formatter->formatList($documents);
        foreach ($documents as $k => $d) {
            $documents[$k]['favorited'] = true;
            $documents[$k]['id'] = $d['id'];
            $documents[$k]['parent'] = (string) $d['parent'];
        }

//        TODO Favorites未使用 先注释
//        $favorite_wikis = WikiFavorites::where('project_key', $project->key)
//            ->where('user.id', $this->user->id)
//            ->get()
//            ->toArray();
//        $favorite_dids = array_column($favorite_wikis, 'wid');
//
//
//        if (isset($myfavorite) && $myfavorite == '1') {
//            $mode = 'search';
//            $favoritedIds = [];
//            foreach ($favorite_dids as $did) {
//                $favoritedIds[] = new ObjectID($did);
//            }
//
//            $query->whereIn('_id', $favoritedIds);
//        }

//        $favorite_wikis = WikiFavorites::where('project_key', $project_key)
//            ->where('user.id', $this->user->id)
//            ->get()
//            ->toArray();
//        $favorite_wids = array_column($favorite_wikis, 'wid');
//
//        foreach ($documents as $k => $d) {
//            if (in_array($d['_id']->__toString(), $favorite_wids)) {
//                $documents[$k]['favorited'] = true;
//            }
//        }
        foreach ($documents as $k => $d) {
            $documents[$k]['favorited'] = true;
        }

        $path = [];
        $home = [];
        if ($directory === 0) {
            $path[] = ['id' => 0, 'name' => 'root'];
            if ($mode === 'list') {
                foreach ($documents as $doc) {
                    if ((! isset($doc['d']) || $doc['d'] != 1) && strtolower($doc['name']) === 'home') {
                        $home = $doc;
                    }
                }
            }
        } else {
            $d = $this->dao->firstParent($project->key, $directory);
            if ($d && isset($d->pt)) {
                $path = $this->getPathTreeDetail($d->pt);
            }
            $path[] = ['id' => $directory, 'name' => $d['name']];
        }
        return [$documents, $path, $home];
    }

    public function destroy(int $id, Project $project, User $user)
    {
        $model = $this->dao->firstParent($project->key, $id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if (isset($model->d) && $model->d == 1) {
            if (! di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
        }

        $model->del_flag = StatusConstant::DELETED;
        $model->save();

//        TODO 暂未使用，先不管 PT， 缺少 WikiEvent 数据表
//        if (isset($document['d']) && $document['d'] === 1)
//        {
//            DB::collection('wiki_' . $project->key)->whereRaw([ 'pt' => $id ])->update([ 'del_flag' => 1 ]);
//        }
//
//        $user = [ 'id' => $this->user->id, 'name' => $this->user->first_name, 'email' => $this->user->email ];
//        Event::fire(new WikiEvent($project->key, $user, [ 'event_key' => 'delete_wiki', 'wiki_id' => $id ]));

        return $id;
    }

    public function update(array $input, int $id, Project $project, User $user)
    {
        $name = $input['name'] ?? '';
        if (empty($name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        $model = $this->dao->firstParent($project->key, $id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if (isset($model->d) && $model->d == 1) {
            if (! di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
        }
//        else{
//            //暂时未使用 checkin 字段
//            if (isset($model['checkin']) && isset($model['checkin']['user']) && $model['checkin']['user']['id'] !== $this->user->id) {
//                throw new \UnexpectedValueException('the object has been locked.', -11955);
//            }
//        }

        if ($model->name !== $name) {
            $isExists = $this->dao->existsUpdateInWikiName($project->key, $model->parent, $name, $model->d);
            if ($isExists) {
                throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
            }
            $model->name = $name;
        }

        if (! isset($model->d) || $model->d !== 1) {
            $contents = $input['contents'] ?? '';
            if (isset($contents) && $contents) {
                $model->contents = $contents;
            }

            if (isset($model->version) && $model->version) {
                $model->version = $model->version + 1;
            } else {
                $model->version = 2;
            }
        }

        $model->editor = Json::encode($user);
        $model->save();

        // record the version
        if (! isset($model['d']) || $model['d'] !== 1) {
//            // unlock the wiki
//            DB::collection('wiki_' . $project_key)->where('_id', $id)->unset('checkin');
//            // record versions
//
//            // insert version
//            $this->recordVersion($project_key, $model);
//
//            $isSendMsg = $request->input('isSendMsg') && true;
//            Event::fire(new WikiEvent($project_key, $updValues['editor'], ['event_key' => 'edit_wiki', 'isSendMsg' => $isSendMsg, 'data' => ['wiki_id' => $id]]));

            return $this->show($input, $model, $user);
        }

        return [$model, []];
    }

//    public function searchPath(array $input, Project $project)
//    {
//        $directories = $this->dao->searchPath($project->key, $input['s'], $input['moved_path'] ?? '');
//        if (empty($directories)){
//            return [];
//        }
//
//        $ds =[];
//        foreach ($directories as $d) {
//           $ds[] = $d->d;
//        }
//
//        $ret = [];
//        foreach ($directories as $d) {
//            $parents = [];
//            $path = '';
//            //这里不对
//            $ps = $this->dao->getName($project->key, $d->d);
//
//            foreach ($ps as $val) {
//                $parents[$val->id] = $val->name;
//            }
//
//            foreach ($d->pt as $pid) {
//                if (isset($parents[$pid])) {
//                    $path .= '/' . $parents[$pid];
//                }
//            }
//            $path .= '/' . $d->name;
//            $ret[] = [ 'id' => $d->id, 'name' => $path ];
//        }
//
//        return $ret;
//    }
}
