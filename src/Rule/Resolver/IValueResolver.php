<?php

namespace srag\Plugins\SrCourseManager\Rule\Resolver;

use srag\Plugins\SrCourseManager\Rule\Comparison\IComparison;

/**
 * Interface IValueResolver
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface must be implemented by any value-resolver within this
 * @package srag\Plugins\SrCourseManager\Rule\Resolver.
 *
 * Value-resolvers are used to resolve dynamic attribute values which then
 * are used for comparisons. Resolver can support different kinds of attributes
 * which must be specified by @see IValueResolver::getAttributes().
 *
 * To help validate dynamic attributes @see IValueResolver::validateValue() can
 * be used to check if the given value is supported by the resolver implementation.
 */
interface IValueResolver
{
    /**
     * returns the resolved lhs-value for the given comparison.
     *
     * @param IComparison $comparison
     * @return mixed
     */
    public function resolveLhsValue(IComparison $comparison);

    /**
     * returns the resolved rhs-value for the given comparison.
     *
     * @param IComparison $comparison
     * @return mixed
     */
    public function resolveRhsValue(IComparison $comparison);

    /**
     * returns all attributes supported by the resolver implementation.
     *
     * @return string[]
     */
    public function getAttributes() : array;

    /**
     * checks whether or not a given value is supported by the resolver implementation.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateValue($value) : bool;
}