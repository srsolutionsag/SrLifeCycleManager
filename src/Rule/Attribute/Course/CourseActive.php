<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseActive extends CourseAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_BOOL,
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_BOOL:
                return $this->course->isActivated();

            case self::COMPARABLE_VALUE_TYPE_INT:
                return ($this->course->isActivated()) ? 1 : 0;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return ($this->course->isActivated()) ? 'true' : 'false';

            default:
                return null;
        }
    }
}