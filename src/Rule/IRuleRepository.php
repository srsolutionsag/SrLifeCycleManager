<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Describes the CRUD operations of a rule.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRuleRepository
{
    /**
     * Fetches an existing rule from the database for the given id.
     *
     * @param int $rule_id
     * @return IRule|null
     */
    public function get(int $rule_id) : ?IRule;

    /**
     * Fetches all existing rules from the database that are related
     * to the given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool     $array_data
     * @return IRule[]
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array;

    /**
     * Fetches all rules that are related to a routine which affects the given
     * object (ref-id) and is of the given routine-type.
     *
     * @param int    $ref_id
     * @param string $routine_type
     * @return IRule[]
     */
    public function getByRefIdAndRoutineType(int $ref_id, string $routine_type) : array;

    /**
     * Creates or updates an existing rule in the database and relates
     * it to set routine.
     *
     * @param IRule    $rule
     * @return IRule
     */
    public function store(IRule $rule) : IRule;

    /**
     * Deletes an existing rule from the database, and it's relation
     * to the set routine (manually because ILIAS doesn't implement
     * constraints yet).
     *
     * @param IRule    $rule
     * @return bool
     */
    public function delete(IRule $rule) : bool;

    /**
     * Initializes and returns an empty rule object.
     *
     * @param IRoutine $routine
     * @return IRule
     */
    public function empty(IRoutine $routine) : IRule;
}