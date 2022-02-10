<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class BoolAttribute extends StaticAttribute
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = (bool) $value;
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_BOOL,
            self::COMPARABLE_VALUE_TYPE_STRING,
            self::COMPARABLE_VALUE_TYPE_INT,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_BOOL:
                return $this->value;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return ($this->value) ? 'true' : 'false';

            case self::COMPARABLE_VALUE_TYPE_INT:
                return ($this->value) ? 1 : 0;

            default:
                return null;
        }
    }
}