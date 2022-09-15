<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupAge extends GroupAttribute
{
    use DateTimeHelper;

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
        $creation_date = $this->getDateTime($this->group->getCreateDate());
        if (null === $creation_date) {
            return null;
        }

        $elapsed_days = $this->getGap($creation_date, $this->getCurrentDate());

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return $elapsed_days;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return (string) $elapsed_days;

            default:
                return null;
        }
    }
}