<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractComparison;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\ResolverFactory;

/**
 * Class CourseComparison
 * @package srag\Plugins\SrLifeCycleManager\Rule\Evaluation\Course
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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
     * @param ResolverFactory $resolvers
     * @param IRule           $rule
     * @param \ilObjCourse    $course
     * @param \ilObjUser      $user
     */
    public function __construct(ResolverFactory $resolvers, IRule $rule, \ilObjCourse $course, \ilObjUser $user)
    {
        $this->course = $course;
        $this->user   = $user;

        parent::__construct($resolvers, $rule);
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