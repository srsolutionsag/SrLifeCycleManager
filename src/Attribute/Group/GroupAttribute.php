<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class GroupAttribute implements IAttribute
{
    /**
     * @var ilObjGroup
     */
    protected $group;

    /**
     * @param ilObjGroup $group
     */
    public function __construct(ilObjGroup $group)
    {
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function getType() : string
    {
        return self::class;
    }

    /**
     * @inheritDoc
     */
    public function getValue() : string
    {
        return static::class;
    }
}