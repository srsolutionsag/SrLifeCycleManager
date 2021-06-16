<?php

namespace srag\Plugins\SrCourseManager\Rule\Comparison;

use srag\Plugins\SrCourseManager\Rule\IRule;
use srag\Plugins\SrCourseManager\Rule\Resolver\Common\CommonValueResolver;

/**
 * Interface IComparison
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface must be implemented by any comparison within this
 * @package srag\Plugins\SrCourseManager\Rule\Comparison.
 *
 * Comparisons are used to check whether rules are applicable or not. A comparison
 * implements the process of comparing an @see IRule lhs- and rhs-value according
 * to the declared operator correctly.
 *
 * Because rule lhs- and rhs-values could contain dynamic attributes, such as
 * course-attributes for example, we must distinguish between different presets.
 * Therefore an implementation of @see IComparison can declare of what attribute types
 * it's aware of, which must be passed into the constructor.
 * Every comparison is at least aware of common values, resolved by @see CommonValueResolver.
 *
 * The method @see IComparison::compare() is the final step of any comparison and returns
 * whether or not a rule is applicable for the passed information.
 */
interface IComparison
{
    /**
     * @return IRule
     */
    public function getRule() : IRule;

    /**
     * @return bool
     */
    public function compare() : bool;
}