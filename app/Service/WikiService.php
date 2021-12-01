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
use Han\Utils\Service;

class WikiService extends Service
{

    public function createDoc(array $input)
    {
        $insValues = [];

        $parent = $input['parent'] ?? '';
        $projectKey = $input['project_key'] ?? '';
        if (!isset($parent)) {
            throw new BusinessException(ErrorCode::PARENT_NOT_EMPTY);

        }
        $insValues['parent'] = $parent;

        if ($parent !== '0') {
            $isExists = DB::collection('wiki_' . $projectKey)
                ->where('_id', $parent)
                ->where('d', 1)
                ->where('del_flag', '<>', 1)
                ->exists();
            if (!$isExists) {
                throw new BusinessException(ErrorCode::PARENT_NOT_EXIST);
            }
        }

        $name = $input['name'] ?? '';
        if (!isset($name) || empty($name)) {
            throw new \UnexpectedValueException('the name can not be empty.', -11952);
        }
        $insValues['name'] = $name;

        $isExists = DB::collection('wiki_' . $projectKey)
            ->where('parent', $parent)
            ->where('name', $name)
            ->where('d', '<>', 1)
            ->where('del_flag', '<>', 1)
            ->exists();
        if ($isExists)
        {
            throw new \UnexpectedValueException('the name cannot be repeated.', -11953);
        }

        $contents = $request->input('contents');
        if (isset($contents) && $contents)
        {
            $insValues['contents'] = $contents;
        }

        $insValues['pt'] = $this->getPathTree($projectKey, $parent);
        $insValues['version'] = 1;
        $insValues['creator'] = [ 'id' => $this->user->id, 'name' => $this->user->first_name, 'email' => $this->user->email ];
        $insValues['created_at'] = time();
        $id = DB::collection('wiki_' . $projectKey)->insertGetId($insValues);

        $isSendMsg = $request->input('isSendMsg') && true;
        Event::fire(new WikiEvent($projectKey, $insValues['creator'], [ 'event_key' => 'create_wiki', 'isSendMsg' => $isSendMsg, 'data' => [ 'wiki_id' => $id->__toString() ] ]));

        return $this->show($request, $projectKey, $id);
    }

    public function getPathTree($projectKey, $directory)
    {
        $pt = [];
        if ($directory === '0') {
            $pt = [ '0' ];
        } else {
            $d = DB::collection('wiki_' . $projectKey)
                ->where('_id', $directory)
                ->first();
            $pt = array_merge($d['pt'], [ $directory ]);
        }
        return $pt;
    }

    public function isPermissionAllowed($project_key, $permission, User $user)
    {
        $uid = isset($user_id) && $user_id ? $user_id : $this->user->id;

        $isAllowed = Acl::isAllowed($uid, $permission, $project_key);
        if (!$isAllowed && in_array($permission, [ 'view_project', 'manage_project' ]))
        {
            if ($this->user->email === 'admin@action.view')
            {
                return true;
            }

            $project = Project::where([ 'key' => $project_key ])->first();
            if ($project && isset($project->principal) && isset($project->principal['id']) && $uid === $project->principal['id'])
            {
                return true;
            }
        }
        return $isAllowed;
    }

}
