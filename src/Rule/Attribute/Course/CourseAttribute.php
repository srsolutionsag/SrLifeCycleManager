<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class CourseAttribute implements IAttribute
{
    /**
     * @var ilObjCourse
     */
    protected $course;

    /**
     * @param ilObjCourse $course
     */
    public function __construct(ilObjCourse $course)
    {
        $this->course = $course;
    }
}