<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NullAttribute extends StaticAttribute
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_NULL,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        return null;
    }
}