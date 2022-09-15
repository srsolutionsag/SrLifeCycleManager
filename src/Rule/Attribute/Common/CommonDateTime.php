<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonDateTime extends CommonAttribute
{
    use DateTimeHelper;

    /**
     * @var DateTimeImmutable|null
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $this->getDate($value);
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
                return $this->value;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return $this->getMysqlDateString($this->value);

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $this->value->getTimestamp();

            default:
                return null;
        }
    }
}