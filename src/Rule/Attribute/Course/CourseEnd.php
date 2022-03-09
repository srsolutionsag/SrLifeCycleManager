<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseEnd extends CourseAttribute
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
        $course_end = $this->course->getCourseEnd();
        if (null === $course_end) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return (DateTime::createFromFormat(
                    self::COMPARABLE_DATETIME_FORMAT,
                    $course_end->get(IL_CAL_DATE)
                )) ?: null;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $course_end->get(IL_CAL_DATE);

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $course_end->get(IL_CAL_UNIX);

            default:
                return null;
        }
    }
}