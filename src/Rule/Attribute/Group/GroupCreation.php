<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupCreation extends GroupAttribute
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
        $creation_string = $this->group->getCreateDate();
        $creation_date = DateTime::createFromFormat('Y-m-d h:i:s', $creation_string);
        if (false === $creation_date) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return $creation_date;

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $creation_date->getTimestamp();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $creation_date->format(self::COMPARABLE_DATETIME_FORMAT);

            default:
                return null;
        }
    }
}