<?php

namespace srag\Plugins\SrCourseManager\Rule\Resolver\User;

/**
 * Interface IUserAware
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface indicates that a @see IComparison is user-aware and could
 * contain dynamic attributes which must be resolved by @see UserValueResolver.
 *
 * Therefore comparisons that implement this interface must provide a method
 * that returns the user-object of the current comparison.
 */
interface IUserAware
{
    /**
     * returns the user-object of the current comparison.
     *
     * @return \ilObjUser
     */
    public function getUser() : \ilObjUser;
}