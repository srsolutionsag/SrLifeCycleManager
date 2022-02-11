<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use ilDateTimeException;
use ilDateTime;
use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonDateTime extends CommonAttribute
{
    /**
     * @var ilDateTime|null
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        try {
            $this->value = new ilDateTime($value, IL_CAL_DATE);
        } catch (ilDateTimeException $e) {
            $this->value = null;
        }
    }

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
        if (null === $this->value) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_DATE:
                return (DateTime::createFromFormat(
                    self::COMPARABLE_DATETIME_FORMAT,
                    $this->value->get(IL_CAL_DATE)
                )) ?: null;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $this->value->get(IL_CAL_DATETIME);

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $this->value->get(IL_CAL_UNIX);

            default:
                return null;
        }
    }
}