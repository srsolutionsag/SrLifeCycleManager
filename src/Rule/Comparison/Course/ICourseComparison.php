<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course\ICourseAware;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\User\IUserAware;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\Taxonomy\ITaxonomyAware;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * Interface ICourseComparison
 * @package srag\Plugins\SrLifeCycleManager\Rule\Evaluation\Course
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ICourseComparison extends IComparison, ICourseAware, ITaxonomyAware, IUserAware
{
    /**
     * @param IRule        $rule
     * @param \ilObjCourse $course
     * @param \ilObjUser   $user
     */
    public function __construct(IRule $rule, \ilObjCourse $course, \ilObjUser $user);
}