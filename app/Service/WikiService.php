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
use App\Constants\WikiConstant;
use App\Exception\BusinessException;
use App\Model\Project;
use App\Model\User;
use App\Model\Wiki;
use App\Model\WikiFavorite;
use App\Service\Dao\WikiDao;
use App\Service\Dao\WikiFavoriteDao;
use App\Service\Formatter\UserFormatter;
use App\Service\Formatter\WikiFavoriteFormatter;
use App\Service\Formatter\WikiFormatter;
use App\Service\Struct\Image;
use Carbon\Carbon;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;
use League\Flysystem\FilesystemOperator;

class WikiService extends Service
{
    #[Inject]
    protected WikiDao $dao;

    #[Inject]
    protected WikiFormatter $formatter;

    #[Inject]
    protected FilesystemOperator $file;

    public function createDoc(array $input, User $user, Project $project)
    {
        $parentId = (int) ($input['parent'] ?? null);
        $name = $input['name'] ?? '';
        $contents = $input['contents'] ?? '';

        if ($parentId !== 0) {
            if (! $this->dao->exists($project->key, $parentId)) {
                throw new BusinessException(ErrorCode::PARENT_NOT_EXIST);
            }
        }

        if (empty($name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        if ($this->dao->existsNameInSameParent($project->key, $parentId, $name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $model = new Wiki();
        $model->project_key = $project->key;
        $model->parent = $parentId;
        $model->d = ProjectConstant::WIKI_CONTENTS;
        $model->del_flag = StatusConstant::NOT_DELETED;
        $model->name = $name;
        $model->pt = $this->getPathTree($this->dao->firstProjectKeyId($parentId));
        $model->version = 1;
        $model->creator = di()->get(UserFormatter::class)->small($user);
        $model->editor = [];
        $model->attachments = [];
        $model->checkin = [];
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

    public function show(array $input, Wiki $model, User $user)
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
        $model->attachments = [];
        $model->checkin = [];
        $model->del_flag = StatusConstant::NOT_DELETED;
        $model->save();

        return $this->formatter->base($model);
    }

    public function getDirTree(string $curNode, array $dt, Project $project)
    {
        $pt = ['0'];
        if (empty($curNode) && $curNode !== WikiConstant::ROOT_PATH) {
            $node = $this->dao->firstProject($project->key);
//            TODO 目前只了解到  $curNode='root'
//            else {
//                $node = $this->dao->firstProjectKeyId($curNode);
//            }

            if ($node) {
                $pt = $node->pt;
                if (isset($node->d) && $node->d == ProjectConstant::WIKI_FOLDER) {
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
                'id' => (string) $val['id'],
                'name' => $val['name'],
                'd' => isset($val['d']) ? $val['d'] : ProjectConstant::WIKI_CONTENTS,
                'parent' => (string) (isset($val['parent']) ? $val['parent'] : ''),
                'toggled' => false,
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

    public function index(array $input, int $directory, Project $project, User $user)
    {
        $favoriteModel = di(WikiFavoriteDao::class)->search($directory, $user->id);
        $favoriteModel = di(WikiFavoriteFormatter::class)->formatList($favoriteModel);
        $widS = array_column($favoriteModel, 'wid');

        $myFavorite = $input['myfavorite'] ?? 0;
        $mode = 'list';
        $favoritedIds = [];

        if (isset($input['name']) || isset($input['contents']) || isset($input['updated_at']) || $myFavorite == '1') {
            $mode = 'search';
        }
        foreach ($widS as $wid) {
            $favoritedIds[] = $wid;
        }

        $documents = $this->dao->search($input, $project->key, $directory, $mode, $favoritedIds);
        if (empty($documents)) {
            return ['', '', ''];
        }
        $documents = $this->formatter->formatList($documents);

        foreach ($documents as $k => $d) {
            if (in_array($d['id'], $widS)) {
                $documents[$k]['favorited'] = true;
            }
        }

        $path = [];
        $home = [];
        if ($directory === WikiConstant::ROOT) {
            $path[] = ['id' => 0, 'name' => 'root'];
            if ($mode === 'list') {
                foreach ($documents as $doc) {
                    if ((! isset($doc['d']) || $doc['d'] != ProjectConstant::WIKI_FOLDER) && strtolower($doc['name']) === 'home') {
                        $home = $doc;
                    }
                }
            }
        } else {
            $d = $this->dao->firstProjectKeyId($directory);
            if ($d && isset($d->pt)) {
                $path = $this->getPathTreeDetail($d->pt);
            }
            $path[] = ['id' => $directory, 'name' => $d['name']];
        }

        return [$documents, $path, $home];
    }

    public function destroy(int $id, Project $project, User $user)
    {
        $model = $this->dao->firstProjectKeyId($id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if ($model->d == ProjectConstant::WIKI_FOLDER) {
            if (! di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
        }

        $model->del_flag = StatusConstant::DELETED;
        $model->save();

        if (isset($model->d) && $model->d === ProjectConstant::WIKI_FOLDER) {
            $this->dao->updateDelFlag($project->key, $id);
        }
        return $id;
//        TODO 暂未使用，先不管 PT， 缺少 WikiEvent 数据表
//        $user = [ 'id' => $this->user->id, 'name' => $this->user->first_name, 'email' => $this->user->email ];
//        Event::fire(new WikiEvent($project->key, $user, [ 'event_key' => 'delete_wiki', 'wiki_id' => $id ]));
    }

    public function update(array $input, int $id, Project $project, User $user)
    {
        $name = $input['name'] ?? '';
        if (empty($name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        $model = $this->dao->firstProjectKeyId($id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if (isset($model->d) && $model->d == ProjectConstant::WIKI_FOLDER) {
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

        if (! isset($model->d) || $model->d !== ProjectConstant::WIKI_FOLDER) {
            $contents = $input['contents'] ?? '';
            if (isset($contents) && $contents) {
                $model->contents = $contents;
            }

            if ($model->version) {
                $model->version = $model->version + 1;
            } else {
                $model->version = 2;
            }
        }

        $model->editor = di()->get(UserFormatter::class)->small($user);
        $model->save();

        // record the version
        if (! isset($model['d']) || $model['d'] !== ProjectConstant::WIKI_FOLDER) {
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

    public function searchPath(array $input, Project $project)
    {
        $directories = $this->dao->searchPath($project->key, $input['s'], (int) ($input['moved_path'] ?? 0));
        if (empty($directories)) {
            return [];
        }

        $ret = [];
        foreach ($directories as $d) {
            $parents = [];
            $path = '';
            $ps = $this->dao->getName($project->key, $d->pt);
            foreach ($ps as $val) {
                $parents[$val->id] = $val->name;
            }
            foreach ($d->pt as $pid) {
                if (isset($parents[$pid])) {
                    $path .= '/' . $parents[$pid];
                }
            }
            $path .= '/' . $d->name;
            $ret[] = ['id' => $d->id, 'name' => $path];
        }

        return $ret;
    }

    public function copy(array $input, Project $project, User $user)
    {
        $id = $input['id'] ?? 0;
        if (! $id) {
            throw new BusinessException(ErrorCode::WIKI_COPY_OBJECT_NOT_EMPTY);
        }

        $name = $input['name'] ?? '';
        if (! $name) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        $destPath = (int) ($input['dest_path'] ?? 0);
        if (! $destPath) {
            throw new BusinessException(ErrorCode::WIKI_DESK_DIT_NOT_EMPTY);
        }

        $document = $this->dao->firstProjectKeyIdDir($id, false);
        if (! $document) {
            throw new BusinessException(ErrorCode::WIKI_COPY_OBJECT_NOT_EXIST);
        }

        $destDirectory = [];
        if ($destPath !== 0) {
            $destDirectory = $this->dao->firstProjectKeyIdDir($id, true);
            if ($destDirectory) {
                throw new BusinessException(ErrorCode::WIKI_DESK_DIR_NOT_EXIST);
            }
        }

        if ($this->dao->existsNameInSameParent($project->key, $destPath, $name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $wiki = new WIki();
        $wiki->project_key = $document->project_key;
        $wiki->d = $document->d;
        $wiki->del_flag = $document->del_flag;
        $wiki->name = $name;
        $wiki->parent = $destPath;
        $wiki->pt = array_merge($destDirectory->pt ?? [], [$destPath]);
        $wiki->version = 1;
        $wiki->contents = $document->contents ?? '';
        $wiki->creator = di()->get(UserFormatter::class)->small($user);
        $wiki->editor = [];
        $wiki->attachments = $document->attachments ?? [];
        $wiki->checkin = [];
        $wiki->save();

        return $this->formatter->base($wiki);
    }

    public function upload($data, int $id, User $user)
    {
        $dir = date('Y/m/d');
        $image = Image::makeFromBase64Data($data, BASE_PATH . '/runtime/' . $dir);
        $object = $image->toAvatarPath();
        $this->file->writeStream($path = $dir . '/' . uniqid() . '.' . $image->getExtension(), $stream = fopen($object, 'r+'));
        fclose($stream);

        $model = $this->dao->firstProjectKeyId($id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        $data = [];
        $data['id'] = $path;
        $data['name'] = $path;
        $data['uploader'] = di()->get(UserFormatter::class)->small($user);

        $attachments = [];
        if (isset($model->attachments) && empty($model->attachments)) {
            $attachments = $model->attachments;
        }

        $attachments[] = $data;
        $model->attachments = $attachments;
        $model->save();

        return $data;
    }

    public function checkin(array $input, int $id, User $user)
    {
        $model = $this->dao->firstProjectKeyId($id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if (! empty($model->checkin)) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_HAS_BEEN_LOCKED);
        }

        $checkin = [];
        $checkin['user'] = di()->get(UserFormatter::class)->small($user);
        $checkin['at'] = Carbon::now()->timestamp;

        $model->checkin = $checkin;
        $model->save();

        return $this->show($input, $model, $user);
    }

    public function checkout(array $input, int $id, Project $project, User $user)
    {
        $model = $this->dao->firstProjectKeyId($id);
        if (empty($model)) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }

        if (isset($model->checkin) && ! ((isset($model->checkin['user']) && $model->checkin['user']['id']) || di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project))) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_CANNOT_BEEN_UNLOCKED);
        }

        $model->checkin = [];
        $model->save();

        return $this->show($input, $model, $user);
    }

    public function favorite(bool $flag, int $id, User $user)
    {
        $userInfo = di(UserFormatter::class)->small($user);
        $model = $this->dao->firstProjectKeyId($id);
        if (empty($model)) {
            throw new BusinessException(ErrorCode::WIKI_OBJECT_NOT_EXIST);
        }
        $favoritesModel = di(WikiFavoriteDao::class)->first($id, $user->id);
        if (! $flag) {
            $favoritesModel->delete();
            return [$id, $userInfo];
        }

        if (! $favoritesModel) {
            $favoritesModel = new WikiFavorite();
        }

        $favoritesModel->wid = $id;
        $favoritesModel->user_id = $user->id;
        $favoritesModel->user = $userInfo;
        $favoritesModel->save();

        return [$id, $userInfo];
    }

    public function getDirChildren(int $id, Project $project)
    {
        $models = $this->dao->getParentWiki($project->key, $id);
        return $this->formatter->formatList($models);
    }

    public function move(array $input, Project $project, User $user)
    {
        $id = $input['id'] ?? 0;
        if (! $id) {
            throw new BusinessException(ErrorCode::WIKI_MOVE_OBJECT_NOT_EMPTY);
        }

        $destPath = (int) ($input['dest_path'] ?? 0);
        if (! isset($destPath)) {
            throw new BusinessException(ErrorCode::WIKI_MOVE_DIR_DEST_NOT_EMPTY);
        }

        $model = $this->dao->firstProjectKeyId($id);
        if (! $model) {
            throw new BusinessException(ErrorCode::WIKI_MOVE_OBJECT_NOT_EXIST);
        }

        if ($model->d == ProjectConstant::WIKI_FOLDER) {
            if (! di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
        }

        $destDirectory = (object) [];
        if ($destPath !== 0) {
            $destDirectory = $this->dao->firstProjectKeyId($id, true);
            if (! $destDirectory) {
                throw new BusinessException(ErrorCode::WIKI_MOVE_DIR_NOT_EXIST);
            }
        }

        if ($this->dao->existsUpdateInWikiName($project->key, $destPath, $model->name, $model->d)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $updValues = [];
        $updValues['parent'] = $destPath;
        $updValues['pt'] = Json::encode(array_merge($destDirectory->pt ?? [], [$destPath]));
        $this->dao->updateMove($id, $updValues);

        if (isset($document['d']) && $document['d'] === 1) {
            $subs = $this->dao->getMove($project->key, $id);
            foreach ($subs as $sub) {
                $pt = isset($sub->pt) ? $sub->pt : [];
                $pind = array_search($id, $pt);
                if ($pind !== false) {
                    $tail = array_slice($pt, $pind);
                    $pt = array_merge($updValues['pt'], $tail);
                    $this->dao->updateMove($sub->id, ['pt' => $pt]);
                }
            }
        }

        return $model;
    }
}
