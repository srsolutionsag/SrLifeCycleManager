<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectCreation extends ObjectAttribute
{
    use DateTimeHelper;

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
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
        $creation_date = $this->getDateTime($this->getObject()->getCreateDate());
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
