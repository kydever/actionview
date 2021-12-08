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
namespace App\Service\Struct;

use App\Model\OswfCurrentstep;
use App\Model\OswfDefinition;
use App\Model\OswfEntry;
use App\Model\User;
use App\Service\Dao\OswfDefinitionDao;
use App\Service\Struct\Workflow\ActionNotAvailableException;
use App\Service\Struct\Workflow\ActionNotFoundException;
use App\Service\Struct\Workflow\ConfigNotFoundException;
use App\Service\Struct\Workflow\CurrentStepNotFoundException;
use App\Service\Struct\Workflow\EntryNotFoundException;
use App\Service\Struct\Workflow\FunctionNotFoundException;
use App\Service\Struct\Workflow\JoinNotFoundException;
use App\Service\Struct\Workflow\ResultNotAvailableException;
use App\Service\Struct\Workflow\ResultNotFoundException;
use App\Service\Struct\Workflow\SplitNotFoundException;
use App\Service\Struct\Workflow\StateNotActivatedException;
use App\Service\Struct\Workflow\StepNotFoundException;
use App\Workflow\Eloquent\CurrentStep;
use App\Workflow\Eloquent\Definition;
use App\Workflow\Eloquent\Entry;
use App\Workflow\Eloquent\HistoryStep;
use Hyperf\Database\Model\Collection;

class Workflow
{
    /**
     * The workflow five states.
     *
     * @var int
     */
    public const OSWF_CREATED = 1;

    public const OSWF_ACTIVATED = 2;

    public const OSWF_SUSPENDED = 3;

    public const OSWF_COMPLETED = 4;

    public const OSWF_KILLED = 5;

    /**
     * The workflow config description.
     */
    protected array $config;

    /**
     * workflow options.
     */
    protected array $options = [];

    /**
     * workflow constructor.
     *
     * @param string $entry_id
     */
    public function __construct(protected ?OswfEntry $entry, ?OswfDefinition $definition = null)
    {
        if ($entry) {
            $definition = $definition ?? di()->get(OswfDefinitionDao::class)->first($this->entry->definition_id, false);
            if (! $definition) {
                throw new ConfigNotFoundException();
            }

            if (isset($definition->contents) && $definition->contents) {
                $this->config = $definition->contents;
            } else {
                throw new ConfigNotFoundException();
            }
        } else {
            throw new EntryNotFoundException();
        }
    }

    /**
     * create workflow.
     *
     * @param string $definition_id
     * @param string $caller
     * @return string
     */
    public static function createInstance(int $definitionId, User $caller)
    {
        $entry = new OswfEntry();
        $entry->definition_id = $definitionId;
        $entry->creator = $caller->toSmall();
        $entry->state = self::OSWF_CREATED;
        $entry->save();

        return new Workflow($entry);
    }

    /**
     * get entry id.
     *
     * @return string
     */
    public function getEntryId()
    {
        return $this->entry->id;
    }

    /**
     * initialize workflow.
     *
     * @param array options
     * @param mixed $options
     */
    public function start($options = [])
    {
        $this->options = array_merge($this->options, $options);

        if (! isset($this->config['initial_action']) || ! $this->config['initial_action']) {
            throw new ActionNotFoundException();
        }

        $actionDescriptor = $this->config['initial_action'];
        if (! $this->isActionAvailable($actionDescriptor)) {
            throw new ActionNotAvailableException();
        }

        // confirm result whose condition is satified.
        if (! isset($actionDescriptor['results']) || ! $actionDescriptor['results']) {
            throw new ResultNotFoundException();
        }

        $available_result_descriptor = $this->getAvailableResult($actionDescriptor['results']);
        if (! $available_result_descriptor) {
            throw new ResultNotAvailableException();
        }
        // create new current step
        $this->createNewCurrentStep($available_result_descriptor, $actionDescriptor['id'], '');
        // change workflow state to activited
        $this->changeEntryState(self::OSWF_ACTIVATED);

        return $this;
    }

    /**
     * get workflow state.
     *
     * @return string
     */
    public function getEntryState()
    {
        return $this->entry->state;
    }

    /**
     * change workflow state.
     *
     * @param string $new_state
     */
    public function changeEntryState($new_state)
    {
        $this->entry->state = $new_state;
        $this->entry->save();
    }

    /**
     * get current steps for workflow.
     *
     * @return Collection<int, OswfCurrentstep>
     */
    public function getCurrentSteps()
    {
        return $this->entry->currentSteps;
    }

    /**
     *  get step meta.
     *
     * @param string $step_id
     * @param mixed $name
     * @return array
     */
    public function getStepMeta($step_id, $name = '')
    {
        $step_description = $this->getStepDescriptor($step_id);
        if ($name) {
            return $step_description[$name] ?? '';
        }
        return $step_description;
    }

    /**
     * execute action.
     *
     * @param string $action_id
     * @param array $options ;
     * @return string
     */
    public function doAction($action_id, $options = [])
    {
        $state = $this->getEntryState($this->entry->id);
        if ($state != self::OSWF_CREATED && $state != self::OSWF_ACTIVATED) {
            throw new StateNotActivatedException();
        }

        $current_steps = $this->getCurrentSteps();
        if (! $current_steps) {
            throw new CurrentStepNotFoundException();
        }

        // set options
        $this->options = array_merge($this->options, $options);
        // complete workflow step transition
        $this->transitionWorkflow($current_steps, intval($action_id));
    }

    /**
     * save workflow configuration info.
     *
     * @param array $info
     */
    public static function saveWorkflowDefinition($info)
    {
        $definition = $info['_id'] ? Definition::find($info['_id']) : new Definition();
        $definition->fill($info);
        $definition->save();
    }

    /**
     * remove configuration info.
     *
     * @param string $definition_id
     */
    public static function removeWorkflowDefinition($definition_id)
    {
        Definition::find($definition_id)->delete();
    }

    /**
     * get all available actions.
     *
     * @param array $info
     * @param bool $dest_state added for kanban dnd, is not common param
     * @param mixed $options
     * @return array
     */
    public function getAvailableActions($options = [], $dest_state = false)
    {
        // set options
        $this->options = array_merge($this->options, $options);

        $available_actions = [];
        // get current steps
        $current_steps = $this->getCurrentSteps();
        foreach ($current_steps as $current_step) {
            $actions = $this->getAvailableActionsFromStep($current_step->step_id, $dest_state);
            $actions && $available_actions += $actions;
        }
        return $available_actions;
    }

    /**
     * get available result from result-list.
     *
     * @param array $results_descriptor
     */
    public function getAvailableResult($results_descriptor): array
    {
        $available_result_descriptor = [];

        // confirm result whose condition is satified.
        foreach ($results_descriptor as $result_descriptor) {
            if (isset($result_descriptor['conditions']) && $result_descriptor['conditions']) {
                if ($this->passesConditions($result_descriptor['conditions'])) {
                    $available_result_descriptor = $result_descriptor;
                    break;
                }
            } else {
                $available_result_descriptor = $result_descriptor;
            }
        }
        return $available_result_descriptor;
    }

    /**
     * get all workflows' name.
     *
     * @return array
     */
    public static function getWorkflowNames()
    {
        return Definition::all(['name']);
    }

    /**
     * get property set.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getPropertySet($key)
    {
        return $key ? $this->entry->propertysets[$key] : $this->entry->propertysets;
    }

    /**
     * add property set.
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function setPropertySet($key, $val)
    {
        $this->entry->propertysets = array_merge($this->entry->propertysets ?: [], [$key => $val]);
        $this->entry->save();
    }

    /**
     * remove property set.
     *
     * @param mixed $key
     */
    public function removePropertySet($key)
    {
        $this->entry->unset($key ? ('propertysets.' . $key) : 'propertysets');
    }

    /**
     * get used states in the workflow.
     *
     * @param mixed $contents
     * @return array
     */
    public static function getStates($contents)
    {
        $state_ids = [];
        $steps = isset($contents['steps']) && $contents['steps'] ? $contents['steps'] : [];
        foreach ($steps as $step) {
            $state_ids[] = isset($step['state']) ? $step['state'] : '';
        }
        return $state_ids;
    }

    /**
     * get used screens in the workflow.
     *
     * @param mixed $contents
     * @return array
     */
    public static function getScreens($contents)
    {
        $screen_ids = [];
        $steps = isset($contents['steps']) && $contents['steps'] ? $contents['steps'] : [];
        foreach ($steps as $step) {
            if (! isset($step['actions']) || ! $step['actions']) {
                continue;
            }
            foreach ($step['actions'] as $action) {
                if (! isset($action['screen']) || ! $action['screen']) {
                    continue;
                }
                $action['screen'] != '-1' && ! in_array($action['screen'], $screen_ids) && $screen_ids[] = $action['screen'];
            }
        }
        return $screen_ids;
    }

    /**
     * get step num.
     *
     * @param mixed $contents
     * @return int
     */
    public static function getStepNum($contents)
    {
        $steps = isset($contents['steps']) && $contents['steps'] ? $contents['steps'] : [];
        return count($steps);
    }

    /**
     * fake new workflow step.
     *
     * @param array $result_descriptor
     * @param array $caller
     */
    public function fakeNewCurrentStep($result_descriptor, $caller)
    {
        $new_current_step = new OswfCurrentstep();
        $new_current_step->entry_id = $this->entry->id;
        $new_current_step->step_id = intval($result_descriptor['id']);
        $new_current_step->status = isset($result_descriptor['status']) ? $result_descriptor['status'] : '';
        $new_current_step->start_time = time();
        $new_current_step->caller = $caller ?: '';
        $new_current_step->save();
    }

    /**
     * complete workflow.
     *
     * @param string $entry_id
     */
    protected function completeEntry($entry_id)
    {
        return $this->changeEntryState($entry_id, self::OSWF_COMPLETED);
    }

    /**
     * check action is available.
     *
     * @param array $descriptor
     * @return bool
     */
    private function isActionAvailable($descriptor)
    {
        if (isset($descriptor['restrict_to'], $descriptor['restrict_to']['conditions']) && $descriptor['restrict_to']['conditions']) {
            if (! $this->passesConditions($descriptor['restrict_to']['conditions'])) {
                return false;
            }
        }
        return true;
    }

    /**
     *  move workflow step to history.
     *
     * @param int $action_id
     * @return string previous_id
     */
    private function moveToHistory(OswfCurrentstep $current_step, $action_id)
    {
        // add to history records
        $history_step = new HistoryStep();
        $history_step->fill($current_step->toArray());
        $history_step->action_id = $action_id;
        $history_step->caller = isset($this->options['caller']) ? $this->options['caller'] : '';
        $history_step->finish_time = time();
        $history_step->save();

        // delete from current step
        $current_step->delete();

        return $history_step->id;
    }

    /**
     *  create new workflow step.
     *
     * @param int $action_id
     * @param string $previous_id
     */
    private function createNewCurrentStep(array $result_descriptor, $action_id, $previous_id = '')
    {
        $step_descriptor = [];
        if (isset($result_descriptor['step']) && $result_descriptor['step']) {
            $step_descriptor = $this->getStepDescriptor($result_descriptor['step']);
            if (! $step_descriptor) {
                throw new StepNotFoundException();
            }
        }
        if (! $step_descriptor) {
            return;
        }
        // order to use for workflow post-function
        if (isset($step_descriptor['state']) && $step_descriptor['state']) {
            $this->options['state'] = $step_descriptor['state'];
        }

        $new_current_step = new OswfCurrentstep();
        $new_current_step->entry_id = $this->entry->id;
        $new_current_step->action_id = $action_id;
        $new_current_step->step_id = isset($result_descriptor['step']) ? intval($result_descriptor['step']) : 0;
        $new_current_step->previous_id = $previous_id;
        $new_current_step->status = $result_descriptor['status'] ?? 'Finished';
        $new_current_step->start_time = time();
        $new_current_step->owners = $this->options['owners'] ?? '';
        $new_current_step->comments = $this->options['comments'] ?? '';
        $new_current_step->caller = $this->options['caller'] ?? '';
        $new_current_step->save();

        // trigger before step
        if (isset($step_descriptor['pre_functions']) && $step_descriptor['pre_functions']) {
            $this->executeFunctions($step_descriptor['pre_functions']);
        }
    }

    /**
     * transfer workflow step.
     *
     * @param array $current_steps
     * @param int $action ;
     * @param mixed $action_id
     */
    private function transitionWorkflow($current_steps, $action_id)
    {
        foreach ($current_steps as $current_step) {
            $step_descriptor = $this->getStepDescriptor($current_step->step_id);
            if (! $step_descriptor) {
                throw new StepNotFoundException();
            }

            $action_descriptor = $this->getActionDescriptor(isset($step_descriptor['actions']) ? $step_descriptor['actions'] : [], $action_id);
            if ($action_descriptor) {
                break;
            }
        }
        if (! $action_descriptor) {
            throw new ActionNotFoundException();
        }
        if (! $this->isActionAvailable($action_descriptor)) {
            throw new ActionNotAvailableException();
        }

        if (! isset($action_descriptor['results']) || ! $action_descriptor['results']) {
            throw new ResultNotFoundException();
        }
        // confirm result whose condition is satified.
        $available_result_descriptor = $this->getAvailableResult($action_descriptor['results']);
        if (! $available_result_descriptor) {
            throw new ResultNotAvailableException();
        }

        // triggers after step
        if (isset($step_descriptor['post_functions']) && $step_descriptor['post_functions']) {
            $this->executeFunctions($step_descriptor['post_functions']);
        }
        // triggers before action
        if (isset($action_descriptor['pre_functions']) && $action_descriptor['pre_functions']) {
            $this->executeFunctions($action_descriptor['pre_functions']);
        }
        // triggers before result
        if (isset($available_result_descriptor['pre_functions']) && $available_result_descriptor['pre_functions']) {
            $this->executeFunctions($available_result_descriptor['pre_functions']);
        }
        // split workflow
        if (isset($available_result_descriptor['split']) && $available_result_descriptor['split']) {
            // get split result
            $split_descriptor = $this->getSplitDescriptor($available_result_descriptor['split']);
            if (! $split_descriptor) {
                throw new SplitNotFoundException();
            }

            // move current to history step
            $prevoius_id = $this->moveToHistory($current_step, $action_id);
            foreach ($split_descriptor['list'] as $result_descriptor) {
                $this->createNewCurrentStep($result_descriptor, $action_id, $prevoius_id);
            }
        } elseif (isset($available_result_descriptor['join']) && $available_result_descriptor['join']) {
            // fix me. join logic will be realized, suggest using the propertyset
            // get join result
            $join_descriptor = $this->getJoinDescriptor($available_result_descriptor['join']);
            if (! $join_descriptor) {
                throw new JoinNotFoundException();
            }

            // move current to history step
            $prevoius_id = $this->moveToHistory($current_step, $action_id);
            if ($this->isJoinCompleted()) {
                // record other previous_ids by propertyset
                $this->createNewCurrentStep($join_descriptor, $action_id, $prevoius_id);
            }
        } else {
            // move current to history step
            $prevoius_id = $this->moveToHistory($current_step, $action_id);
            // create current step
            $this->createNewCurrentStep($available_result_descriptor, $action_id, $prevoius_id);
        }
        // triggers after result
        if (isset($available_result_descriptor['post_functions']) && $available_result_descriptor['post_functions']) {
            $this->executeFunctions($available_result_descriptor['post_functions']);
        }
        // triggers after action
        if (isset($action_descriptor['post_functions']) && $action_descriptor['post_functions']) {
            $this->executeFunctions($action_descriptor['post_functions']);
        }
    }

    /**
     * check if the join is completed.
     */
    private function isJoinCompleted()
    {
        return ! CurrentStep::where('entry_id', $this->entry->id)->exists();
    }

    /**
     * get join descriptor from list.
     *
     * @param string $join_id
     * @return array
     */
    private function getJoinDescriptor($join_id)
    {
        foreach ($this->config['joins'] as $join) {
            if ($join['id'] == $join_id) {
                return $join;
            }
        }
        return [];
    }

    /**
     * get split descriptor from list.
     *
     * @param string $split_id
     * @return array
     */
    private function getSplitDescriptor($split_id)
    {
        foreach ($this->config['splits'] as $split) {
            if ($split['id'] == $split_id) {
                return $split;
            }
        }
        return [];
    }

    /**
     * get action descriptor from list.
     *
     * @param array $actions
     * @param string $action_id
     * @return array
     */
    private function getActionDescriptor($actions, $action_id)
    {
        // get global config
        $actions = $actions ?: [];
        foreach ($actions as $action) {
            if ($action['id'] == $action_id) {
                return $action;
            }
        }
        return [];
    }

    /**
     *  get step configuration.
     *
     * @param array $steps
     * @param string $step_id
     * @return array
     */
    private function getStepDescriptor($step_id)
    {
        foreach ($this->config['steps'] as $step) {
            if ($step['id'] == $step_id) {
                return $step;
            }
        }
        return [];
    }

    /**
     * get available actions for step.
     *
     * @param string $step_id
     * @param bool $dest_state added for kanban dnd, is not common param
     * @return array
     */
    private function getAvailableActionsFromStep($step_id, $dest_state = false)
    {
        $step_descriptor = $this->getStepDescriptor($step_id);
        if (! $step_descriptor) {
            throw new StepNotFoundException();
        }
        if (! isset($step_descriptor['actions']) || ! $step_descriptor['actions']) {
            return [];
        }
        // global conditions for step
        if (! $this->isActionAvailable($step_descriptor)) {
            return [];
        }

        $available_actions = [];
        foreach ($step_descriptor['actions'] as $action) {
            if ($this->isActionAvailable($action)) {
                if ($dest_state) {
                    $state = '';
                    if (isset($action['results']) && is_array($action['results']) && count($action['results']) > 0 && isset($action['results'][0]['step'])) {
                        $dest_step_descriptor = $this->getStepDescriptor($action['results'][0]['step']);
                        $state = $dest_step_descriptor['state'];
                    }

                    $available_actions[] = ['id' => $action['id'], 'name' => $action['name'], 'screen' => $action['screen'] ?: '', 'state' => $state];
                } else {
                    $available_actions[] = ['id' => $action['id'], 'name' => $action['name'], 'screen' => $action['screen'] ?: ''];
                }
            }
        }

        return $available_actions;
    }

    /**
     * check conditions is passed.
     *
     * @param array $conditions
     * @return bool
     */
    private function passesConditions($conditions)
    {
        if (! isset($conditions['list']) || ! $conditions['list']) {
            return true;
        }

        $type = $conditions['type'] ?? 'and';
        $result = $type == 'and';

        foreach ($conditions['list'] as $condition) {
            $tmp = $this->passesCondition($condition);
            if ($type == 'and' && ! $tmp) {
                return false;
            }
            if ($type == 'or' && $tmp) {
                return true;
            }
        }
        return $result;
    }

    /**
     * check condition is passed.
     *
     * @param array $condition
     * @return bool
     */
    private function passesCondition($condition)
    {
        return $this->executeFunction($condition);
    }

    /**
     * execute functions.
     *
     * @param array function
     * @param mixed $functions
     */
    private function executeFunctions($functions)
    {
        if (! $functions || ! is_array($functions)) {
            return;
        }

        foreach ($functions as $function) {
            if (is_array($function) && $function) {
                $this->executeFunction($function);
            }
        }
    }

    /**
     * execute function.
     *
     * @param array $function
     * @return mixed
     */
    private function executeFunction($function)
    {
        $method = explode('@', $function['name']);
        $class = $method[0];
        $action = isset($method[1]) && $method[1] ? $method[1] : 'handle';

        // check handle function exists
        if (! method_exists($class, $action)) {
            throw new FunctionNotFoundException();
        }
        $args = $function['args'] ?? [];
        // generate temporary vars
        $tmp_vars = $this->genTmpVars($args);
        // call handle function
        return $class::$action($tmp_vars);
    }

    /**
     * generate temporary variable.
     *
     * @param mixed $args
     * @return array
     */
    private function genTmpVars($args = [])
    {
        $tmp_vars = [];
        foreach ($this->entry as $key => $val) {
            $tmp_vars[$key] = $val;
        }
        $tmp_vars = array_merge($tmp_vars, $this->options);

        return array_merge($tmp_vars, $args);
    }
}
