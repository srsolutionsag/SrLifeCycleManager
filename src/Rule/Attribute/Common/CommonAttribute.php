<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class CommonAttribute implements IAttribute
{
    /**
     * @var string[]
     */
    public const COMMON_ATTRIBUTES = [
        CommonBoolean::class,
        CommonInteger::class,
        CommonString::class,
        CommonList::class,
        CommonDateTime::class,
        CommonNull::class,
    ];
}