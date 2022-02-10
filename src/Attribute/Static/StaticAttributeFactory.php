<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class StaticAttributeFactory
{
    /**
     * @param mixed $value
     * @return IAttribute
     */
    public function getAttribute($value) : IAttribute
    {
        switch (gettype($value)) {
            case IAttribute::COMPARABLE_VALUE_TYPE_BOOL:
                return new BoolAttribute($value);

            case IAttribute::COMPARABLE_VALUE_TYPE_INT:
                return new IntegerAttribute($value);

            case IAttribute::COMPARABLE_VALUE_TYPE_STRING:
                return new StringAttribute($value);

            case IAttribute::COMPARABLE_VALUE_TYPE_ARRAY:
                return new ArrayAttribute($value);

            case IAttribute::COMPARABLE_VALUE_TYPE_NULL:

            default:
                return new NullAttribute($value);
        }
    }
}