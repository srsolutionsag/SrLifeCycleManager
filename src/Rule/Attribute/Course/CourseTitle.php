<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseTitle extends CourseAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return $this->course->getTitle();
        }

        return null;
    }
}