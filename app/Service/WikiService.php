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
use App\Service\Dao\WikiDao;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

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

        $insValues['pt'] = $this->getPathTree($projectKey, $parent);
        $insValues['version'] = 1;
        $insValues['creator'] = ['id' => $user->id, 'name' => $user->first_name, 'email' => $user->email];
        $insValues['created_at'] = time();

        $model = new Wiki();
        $id = $model->insertGetId($insValues);

//         TODO 需要需改
//        $isSendMsg = $input['isSendMsg'] && true;
//        Event::fire(new WikiEvent($projectKey, $insValues['creator'], ['event_key' => 'create_wiki', 'isSendMsg' => $isSendMsg, 'data' => ['wiki_id' => $id->__toString()]]));

        return $this->show($input, $id, $user);
    }

    public function getPathTree($projectKey, $directory)
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

    public function isPermissionAllowed($project_key, $permission, User $user)
    {
        $uid = isset($user_id) && $user_id ? $user_id : $this->user->id;

        $isAllowed = Acl::isAllowed($uid, $permission, $project_key);
        if (! $isAllowed && in_array($permission, ['view_project', 'manage_project'])) {
            if ($this->user->email === 'admin@action.view') {
                return true;
            }

            $project = Project::where(['key' => $project_key])->first();
            if ($project && isset($project->principal, $project->principal['id']) && $uid === $project->principal['id']) {
                return true;
            }
        }
        return $isAllowed;
    }

    public function show($input, int $id, User $user)
    {
        $document = $this->dao->first($id, true);

        if ($this->dao->existsWidUser($id, $user->id)) {
            $document['favorited'] = true;
        }

        $newest = [];
        $newest['name'] = $document['name'];
        $newest['editor'] = isset($document['editor']) ? $document['editor'] : $document['creator'];
        $newest['updated_at'] = isset($document['updated_at']) ? $document['updated_at'] : $document['created_at'];
        $newest['version'] = $document['version'];

        $v = $input('v');
        if (isset($v) && intval($v) != $document['version']) {
            // 传过来的版本如果和数据表存储的不相等
            $wiki = $this->dao->firstVersion($input['project_key'], $id, $v);
            if ($wiki) {
                throw new \UnexpectedValueException('the version does not exist.', -11957);
            }
            $document['name'] = $wiki['name'];
            $document['contents'] = $wiki['contents'];
            $document['editor'] = $wiki['editor'];
            $document['updated_at'] = $wiki['updated_at'];
            $document['version'] = $wiki['version'];
        }

        $document['versions'] = $this->dao->search($input, $id);
        array_unshift($document['versions'], $newest);

        $path = $this->getPathTreeDetail($input['project_key'], $document['pt']);

        return Response()->json(['ecode' => 0, 'data' => $this->arrange($document), 'options' => ['path' => $path]]);
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
}
