<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course;

/**
 * Interface ICourseAware
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This interface indicates that a @see IComparison is course-aware and could
 * contain dynamic attributes which must be resolved by @see CourseValueResolver.
 *
 * Therefore comparisons that implement this interface must provide a method
 * that returns the course-object of the current comparison.
 */
interface ICourseAware
{
    /**
     * returns the course-object of the current comparison.
     *
     * @return \ilObjCourse
     */
    public function getCourse() : \ilObjCourse;
}