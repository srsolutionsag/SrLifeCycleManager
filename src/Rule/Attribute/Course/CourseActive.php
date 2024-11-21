<?php
/*********************************************************************
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
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_BOOL,
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     * @return bool|int|string|null
     */
    public function getComparableValue(string $type): bool|int|string|null
    {
        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_BOOL => $this->course->isActivated(),
            self::COMPARABLE_VALUE_TYPE_INT => ($this->course->isActivated()) ? 1 : 0,
            self::COMPARABLE_VALUE_TYPE_STRING => ($this->course->isActivated()) ? 'true' : 'false',
            default => null,
        };
    }
}
