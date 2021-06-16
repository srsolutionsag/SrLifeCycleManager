<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Interface IRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface IRuleRepository
{
    /**
     * returns a rule entry from the database for given id.
     *
     * @param int $rule_id
     * @return Rule|null
     */
    public function get(int $rule_id) : ?Rule;

    /**
     * creates or updates a rule entry in the database.
     *
     * @param IRule $rule
     * @return Rule
     */
    public function store(IRule $rule) : Rule;

    /**
     * returns all rules as Rule objects.
     *
     * @return Rule[]|null
     */
    public function getAllAsDTO() : ?array;

    /**
     * returns all rules as array-data.
     *
     * @return array
     */
    public function getAllAsArray() : array;

    /**
     * deletes a rule entry from the database.
     *
     * @param IRule $rule
     * @return bool
     */
    public function delete(IRule $rule) : bool;
}