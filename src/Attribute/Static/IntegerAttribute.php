<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class IntegerAttribute extends StaticAttribute
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
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
        if (self::COMPARABLE_VALUE_TYPE_INT === $type) {
            return $this->value;
        }

        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return (string) $this->value;
        }

        return null;
    }
}