<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course\ICourseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseAttributeFactory
{
    /**
     * @param ICourseRequirement $requirement
     * @param string             $value
     * @return IAttribute
     */
    public function getAttribute(ICourseRequirement $requirement, string $value) : IAttribute
    {
        switch ($value) {
            case CourseStart::class:
                return new CourseStart($requirement->getCourse());

            case CourseEnd::class:
                return new CourseEnd($requirement->getCourse());

            case CourseTitle::class:
                return new CourseTitle($requirement->getCourse());

            case CourseAge::class:
                return new CourseAge($requirement->getCourse());

            case CourseActive::class:
                return new CourseActive($requirement->getCourse());

            case CourseMember::class:
                return new CourseMember($requirement->getCourse());

            case CourseCreation::class:
                return new CourseCreation($requirement->getCourse());

            case CourseMetadata::class:
                return new CourseMetadata($requirement->getDatabase(), $requirement->getCourse());

            case CourseTaxonomy::class:
                return new CourseTaxonomy($requirement->getDatabase(), $requirement->getCourse());

            default:
                return new CommonNull($value);
        }
    }

    /**
     * @return string[]
     */
    public function getAttributeList() : array
    {
        return [
            CourseActive::class,
            CourseTitle::class,
            CourseStart::class,
            CourseEnd::class,
            CourseMember::class,
            CourseTaxonomy::class,
            CourseMetadata::class,
            CourseCreation::class,
            CourseAge::class,
        ];
    }
}