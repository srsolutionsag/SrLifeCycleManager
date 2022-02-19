<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * Interface IRepository
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRuleRepository
{
    /**
     * Returns an existing rule for the given routine and rule id.
     *
     * @param int $routine_id
     * @param int $rule_id
     * @return IRoutineAwareRule|null
     */
    public function get(int $routine_id, int $rule_id) : ?IRoutineAwareRule;

    /**
     * Returns all rules related to the given routine id.
     *
     * To retrieve routines as array-data true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $routine_id
     * @param bool $array_data
     * @return array
     */
    public function getAll(int $routine_id, bool $array_data = false) : array;

    /**
     * @param int $routine_id
     * @return IRoutineAwareRule|null
     */
    public function getEmpty(int $routine_id) : ?IRoutineAwareRule;

    /**
     * Creates or updates a rule entry in the database.
     *
     * @param IRoutineAwareRule $rule
     * @return IRoutineAwareRule
     */
    public function store(IRoutineAwareRule $rule) : IRoutineAwareRule;

    /**
     * Deletes a rule entry from the database and all it's relations
     * (manually because ilias does not support constraints).
     *
     * @param IRoutineAwareRule $rule
     * @return bool
     */
    public function delete(IRoutineAwareRule $rule) : bool;
}