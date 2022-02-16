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
     * returns a rule entry from the database for given id.
     * @param int $id
     * @return IRule|null
     */
    public function get(int $id) : ?IRule;

    /**
     * creates or updates a rule entry in the database.
     * @param IRule $rule
     * @return IRule
     */
    public function store(IRule $rule) : IRule;

    /**
     * returns all rules as RuleDTO objects.
     * @return IRule[]|null
     */
    public function getAllAsDTO() : ?array;

    /**
     * returns all rules as array-data.
     *
     * @return array
     */
    public function getAllAsArray() : array;

    /**
     * returns all rules for the given comparison.
     *
     * @param array $value_types
     * @return array|null
     */
    public function getAllForValueTypes(array $value_types) : ?array;

    /**
     * deletes a rule entry from the database.
     * @param Rule $rule
     * @return bool
     */
    public function delete(IRule $rule) : bool;
}