<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseStart extends CourseAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_DATE,
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        $course_start = $this->course->getCourseStart();
        if (null === $course_start) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return (DateTime::createFromFormat(
                    self::COMPARABLE_DATETIME_FORMAT,
                    $course_start->get(IL_CAL_DATE)
                )) ?: null;

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $course_start->get(IL_CAL_UNIX);

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $course_start->get(IL_CAL_DATETIME);

            default:
                return null;
        }
    }
}