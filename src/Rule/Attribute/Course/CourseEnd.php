<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseEnd extends CourseAttribute
{
    use DateTimeHelper;

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

        $end_date = $this->getDate($course_end->get(IL_CAL_DATE));
        if (null === $end_date) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return $end_date;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $this->getMysqlDateString($end_date);

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $end_date->getTimestamp();

            default:
                return null;
        }
    }
}