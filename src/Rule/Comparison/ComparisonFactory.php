<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Course\CourseComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\ResolverFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use ilObjCourse;
use ilObjUser;

/**
 * Class ComparisonFactory
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ComparisonFactory
{
    /**
     * @var ResolverFactory
     */
    private $resolvers;

    /**
     * ComparisonFactory constructor
     */
    public function __construct()
    {
        $this->resolvers = new ResolverFactory();
    }

    /**
     * @param IRule        $rule
     * @param ilObjCourse $course
     * @param ilObjUser   $user
     * @return CourseComparison
     */
    public function course(IRule $rule, ilObjCourse $course, ilObjUser $user) : CourseComparison
    {
        return new CourseComparison($this->resolvers, $rule, $course, $user);
    }
}