<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ArrayAttribute extends StaticAttribute
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = (array) $value;
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_ARRAY,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        if (self::COMPARABLE_VALUE_TYPE_ARRAY === $type) {
            return $this->value;
        }

        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return implode(',', $this->value);
        }

        return null;
    }
}