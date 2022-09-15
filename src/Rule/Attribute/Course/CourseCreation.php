<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseCreation extends CourseAttribute
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
        $creation_date = $this->getDateTime($this->course->getCreateDate());
        if (null === $creation_date) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return $creation_date;

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $creation_date->getTimestamp();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $this->getMysqlDateString($creation_date);

            default:
                return null;
        }
    }
}