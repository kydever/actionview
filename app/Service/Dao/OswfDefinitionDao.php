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
use App\Model\OswfDefinition;
use App\Model\User;
use App\Service\Struct\Workflow;
use Han\Utils\Service;

class OswfDefinitionDao extends Service
{
    public function first(int $id, bool $throw = false): ?OswfDefinition
    {
        $model = OswfDefinition::query()->find($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::WORKFLOW_NOT_EXISTS);
        }

        return $model;
    }

    public function exists(string $stateKey, int $id): bool
    {
        return OswfDefinition::whereJsonContains('state_ids', $stateKey ?? $id)->exists();
    }

    public function getByFieldsList(string $projectKey, array $fields = [])
    {
        return OswfDefinition::where('project_key', '$_sys_$')
            ->orWhere('project_key', $projectKey)
            ->orderBy('project_key', 'asc')
            ->orderBy('id', 'asc')
            ->get($fields);
    }

    /**
     * @param int $id
     * @return OswfDefinition|null
     */
    public function findById ( int $id ): ?OswfDefinition
    {
        $model = OswfDefinition::findFromCache ( $id );

        return $model;
    }

    public function findBySourceId ( int $sourceId ): ?OswfDefinition
    {
        $model = OswfDefinition::find ( $sourceId );

        return $model;
    }

    public function createOrUpdate ( int $id, User $user, string $projectKey,  array $attributes ): OswfDefinition
    {
        $model = $this->findById($id );
        if ( empty ( $model ) ) {
            $model = new OswfDefinition();
            $model->project_key = $projectKey;
        }
        if ( ! empty ( $attributes [ 'contents' ] ) ) {
            $latest_modifier = [
                'id' => $user->id,
                'name' => $user->first_name,
            ];
            $latest_modified_time = date ( 'Y-m-d H:i:s' );
            $state_ids = Workflow::getScreens($attributes [ 'contents' ]);
            $screen_ids = Workflow::getScreens($attributes [ 'contents' ]);
            $steps = Workflow::getStepNum($attributes [ 'contents' ]);
        } else {
            $latest_modifier = [];
            $latest_modified_time = '';
            $state_ids = [];
            $screen_ids = [];
            $steps = 0;
        }
        if ( ! empty ( $attributes [ 'source_id' ] ) ) {
            $source_definition = $this->findBySourceId($attributes [ 'source_id' ]);
            $latest_modifier = [
                'id' => $user->id,
                'name' => $user->first_name,
            ];
            $latest_modified_time = date ( 'Y-m-d H:i:s' );
            $state_ids = $source_definition->state_ids;
            $screen_ids = $source_definition->screen_ids;
            $steps = $source_definition->steps;
            $contents = $source_definition->contents;
        }

        $model->latest_modifier = $latest_modifier;
        $model->latest_modified_time = $latest_modified_time;
        $model->state_ids = $state_ids;
        $model->screen_ids = $screen_ids;
        $model->steps = $steps;
        $model->contents = $contents ?? $attributes [ 'contents' ];
        $model->name = $attributes [ 'name' ];
        $model->save();

        return $model;
    }
}
