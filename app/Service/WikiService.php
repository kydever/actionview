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
use App\Exception\BusinessException;
use App\Model\User;
use App\Model\Wiki;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\WikiDao;
use Carbon\Carbon;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;

class WikiService extends Service
{
    #[Inject]
    protected WikiDao $dao;

    public function createDoc(array $input, User $user)
    {
        $insValues = [];
        $parent = $input['parent'] ?? '';
        $projectKey = $input['project_key'] ?? '';
        if (! isset($parent)) {
            throw new BusinessException(ErrorCode::PARENT_NOT_EMPTY);
        }
        $insValues['parent'] = $parent;

        if ($parent !== '0') {
            if ($this->dao->existsParent($projectKey, $parent)) {
                throw new BusinessException(ErrorCode::PARENT_NOT_EXIST);
            }
        }

        $name = $input['name'] ?? '';
        if (! isset($name) || empty($name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }

        $insValues['name'] = $name;
        if ($this->dao->existsParentName($projectKey, $parent, $name)) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $contents = $input['contents'];
        if (isset($contents) && $contents) {
            $insValues['contents'] = $contents;
        }

        $insValues['pt'] = Json::encode($this->getPathTree($projectKey, $parent));
        $insValues['version'] = 1;
        $insValues['creator'] = Json::encode(['id' => $user->id, 'name' => $user->first_name, 'email' => $user->email]);
        $insValues['editor'] = '{}';
        $insValues['created_at'] = Carbon::now()->toDateTimeString();
        $insValues['user'] = Json::encode($user);

        $model = new Wiki();
        $id = $model->insertGetId($insValues);

//         TODO 需要需改
//        $isSendMsg = $input['isSendMsg'] && true;
//        Event::fire(new WikiEvent($projectKey, $insValues['creator'], ['event_key' => 'create_wiki', 'isSendMsg' => $isSendMsg, 'data' => ['wiki_id' => $id->__toString()]]));
        return $this->show($input, $id, $user);
    }

    public function getPathTree(string $projectKey, string $directory)
    {
        $pt = [];
        if ($directory === '0') {
            $pt = ['0'];
        } else {
            $d = $this->dao->firstParent($projectKey, $directory);
            $pt = array_merge($d['pt'], [$directory]);
        }
        return $pt;
    }

    public function isPermissionAllowed($projectKey, $permission, User $user)
    {
        $uid = $user->id;

        $project = di(ProjectDao::class)->firstByKey($projectKey);
        $isAllowed = di(AclService::class)->isAllowed($uid, $permission, $project);
        if (! $isAllowed && in_array($permission, ['view_project', 'manage_project'])) {
            if ($user->email === 'admin@action.view') {
                return true;
            }
            $project = di(ProjectDao::class)->firstByKey($projectKey);
            if ($project && isset($project->principal, $project->principal['id']) && $uid === $project->principal['id']) {
                return true;
            }
        }
        return $isAllowed;
    }

    public function show($input, int $id, User $user)
    {
        $document = $this->dao->first($id, true)->toArray();

        if ($this->dao->existsWidUser($id, $user->id)) {
            $document['favorited'] = true;
        }
        $newest = [];
        $newest['name'] = $document['name'];
        $newest['editor'] = isset($document['editor']) ? JSON::decode($document['editor']) : JSON::decode($document['creator']);
        $newest['updated_at'] = isset($document['updated_at']) ? $document['updated_at'] : $document['created_at'];
        $newest['version'] = $document['version'];

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

        $document['versions'] = $this->dao->search($input, $id);

        array_unshift($newest, $document['versions']);

        $path = $this->getPathTreeDetail($input['project_key'], Json::decode($document['pt']));
        return [$this->arrange($document), $path];
    }

    public function getPathTreeDetail($projectKey, $pt)
    {
        $parents = [];
        $ps = $this->dao->getName($projectKey, $pt);
        foreach ($ps as $val) {
            $parents[$val['_id']->__toString()] = $val['name'];
        }

        $path = [];
        foreach ($pt as $pid) {
            if ($pid === '0') {
                $path[] = ['id' => '0', 'name' => 'root'];
            } elseif (isset($parents[$pid])) {
                $path[] = ['id' => $pid, 'name' => $parents[$pid]];
            }
        }
        return $path;
    }

    public function arrange($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $k => $val) {
            $data[$k] = $this->arrange($val);
        }

        return $data;
    }

    public function createFolder(array $input, User $user)
    {
        $insValues = [];

        $parent = $input['parent'];
        if (! isset($parent)) {
            throw new BusinessException(ErrorCode::PARENT_NOT_EMPTY);
        }
        $insValues['parent'] = $parent;

        if ($parent !== '0') {
            if (! $this->dao->existsParent($input['project_key'], $parent)) {
                throw new BusinessException(ErrorCode::PARENT_NOT_EXIST);
            }
        }

        $name = $input['name'] ?? '';
        if (! isset($name) || ! $name) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_EMPTY);
        }
        $insValues['name'] = $name;

        $isExists = $this->dao->existsParentName($input['project_key'], $parent, $name);
        if ($isExists) {
            throw new BusinessException(ErrorCode::WIKI_NAME_NOT_REPEAT);
        }

        $insValues['pt'] = Json::encode($this->getPathTree($input['project_key'], $parent));
        $insValues['d'] = 1;
        $insValues['creator'] = Json::encode(['id' => $user->id, 'name' => $user->first_name, 'email' => $user->email]);
        $insValues['created_at'] = Carbon::now()->toDateTimeString();
        $insValues['user'] = Json::encode($user);
        $insValues['editor'] = '{}';

        $model = new Wiki();
        $id = $model->insertGetId($insValues);
        return $this->dao->first($id);
    }
}
