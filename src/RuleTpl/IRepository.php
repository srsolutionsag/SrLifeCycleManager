<?php

namespace srag\Plugins\SrCourseManager\Rule;

use srag\Plugins\SrCourseManager\Rule\Comparison\IComparison;
use srag\Plugins\SrCourseManager\Rule\RuleDTO;

/**
 * Interface IRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface IRepository
{
    /**
     * returns a rule entry from the database for given id.
     *
     * @param int $id
     * @return RuleDTO|null
     */
    public function get(int $id) : ?RuleDTO;

    /**
     * creates or updates a rule entry in the database.
     *
     * @param RuleDTO $rule
     * @return RuleDTO
     */
    public function store(RuleDTO $rule) : RuleDTO;

    /**
     * returns all rules as RuleDTO objects.
     *
     * @return RuleDTO[]|null
     */
    public function getAllAsEntity() : ?array;

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
     *
     * @param RuleDTO $rule
     * @return bool
     */
    public function delete(RuleDTO $rule) : bool;
}