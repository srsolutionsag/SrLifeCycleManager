<?php

namespace srag\Plugins\SrCourseManager\Rule\Comparison\Course;

use srag\Plugins\SrCourseManager\Rule\Comparison\AbstractComparison;
use srag\Plugins\SrCourseManager\Rule\Comparison\Course\ICourseComparison;
use srag\Plugins\SrCourseManager\Rule\Resolver\Course\CourseValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\User\UserValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\Taxonomy\TaxonomyValueResolver;
use srag\Plugins\SrCourseManager\Rule\IRule;

/**
 * Class CourseComparison
 * @package srag\Plugins\SrCourseManager\Rule\Evaluation\Course
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class CourseComparison extends AbstractComparison implements ICourseComparison
{
    /**
     * @var \ilObjCourse
     */
    private $course;

    /**
     * @var \ilObjUser
     */
    private $user;

    /**
     * CourseComparison constructor.
     *
     * @param IRule        $rule
     * @param \ilObjCourse $course
     * @param \ilObjUser   $user
     */
    public function __construct(IRule $rule, \ilObjCourse $course, \ilObjUser $user)
    {
        $this->course = $course;
        $this->user   = $user;

        parent::__construct($rule);
    }

    /**
     * @inheritDoc
     */
    public function getCourse() : \ilObjCourse
    {
        return $this->course;
    }

    /**
     * @inheritDoc
     */
    public function getObject() : \ilObject
    {
        return $this->course;
    }

    /**
     * @inheritDoc
     */
    public function getUser() : \ilObjUser
    {
        return $this->user;
    }
}