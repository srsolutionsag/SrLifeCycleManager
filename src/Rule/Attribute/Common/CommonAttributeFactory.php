<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonAttributeFactory
{
    /**
     * @param string $type
     * @param mixed  $value
     * @return IAttribute
     */
    public function getAttribute(string $type, $value) : IAttribute
    {
        switch ($type) {
            case CommonBoolean::class:
                return new CommonBoolean($value);

            case CommonInteger::class:
                return new CommonInteger($value);

            case CommonString::class:
                return new CommonString($value);

            case CommonList::class:
                return new CommonList($value);

            case CommonDateTime::class:
                return new CommonDateTime($value);

            default:
                return new CommonNull($value);
        }
    }

    /**
     * @return string[]
     */
    public function getAttributeList() : array
    {
        return CommonAttribute::COMMON_ATTRIBUTES;
    }
}