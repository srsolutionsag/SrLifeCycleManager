<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Course\CourseComparison;

/**
 * Class ComparisonFactory
 * @package srag\Plugins\SrLifeCycleManager\Rule\Comparison
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ComparisonFactory
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * prevents multiple instances
     */
    private function __construct() {}
    private function __wakeup() {}
    private function __clone() {}

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (!isset(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    /**
     * @return CourseComparison
     */
    public function course(IRule $rule, \ilObjCourse $course, \ilObjUser $user) : CourseComparison
    {
        return new CourseComparison($rule, $course, $user);
    }
}