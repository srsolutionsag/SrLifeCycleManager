<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class StaticAttribute implements IAttribute
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @inheritDoc
     */
    public function getValue() : string
    {
        return static::class;
    }

    /**
     * @inheritDoc
     */
    public function getType() : string
    {
        return self::class;
    }
}