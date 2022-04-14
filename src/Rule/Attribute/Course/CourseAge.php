<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseAge extends CourseAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        $creation_string = $this->course->getCreateDate();
        $creation_date = DateTime::createFromFormat('Y-m-d h:i:s', $creation_string);
        if (false === $creation_date) {
            return null;
        }

        // get amount of days elapsed since $creation_date.
        $elapsed_days = $creation_date->diff((new DateTime()))->format("%r%a");

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return (int) $elapsed_days;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $elapsed_days;

            default:
                return null;
        }
    }
}