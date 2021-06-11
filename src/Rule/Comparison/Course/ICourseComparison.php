<?php

namespace srag\Plugins\SrCourseManager\Rule\Comparison\Course;

use srag\Plugins\SrCourseManager\Rule\Comparison\IComparison;
use srag\Plugins\SrCourseManager\Rule\Resolver\Course\ICourseAware;
use srag\Plugins\SrCourseManager\Rule\Resolver\User\IUserAware;
use srag\Plugins\SrCourseManager\Rule\Resolver\Taxonomy\ITaxonomyAware;
use srag\Plugins\SrCourseManager\Rule\IRule;

/**
 * Interface ICourseComparison
 * @package srag\Plugins\SrCourseManager\Rule\Evaluation\Course
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