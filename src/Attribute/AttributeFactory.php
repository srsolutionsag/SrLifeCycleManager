<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use srag\Plugins\SrLifeCycleManager\Dependency\IDependencyPool;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AttributeFactory
{
    /**
     * @var GroupAttributeFactory
     */
    protected $course_factory;

    /**
     * @var GroupAttributeFactory
     */
    protected $group_factory;

    /**
     * @var StaticAttributeFactory
     */
    protected $static_factory;

    /**
     * AttributeFactory constructor
     */
    public function __construct()
    {
        $this->course_factory = new GroupAttributeFactory();
        $this->group_factory = new GroupAttributeFactory();
        $this->static_factory = new StaticAttributeFactory();
    }

    /**
     * @param IDependencyPool $pool
     * @param string         $type
     * @param mixed          $value
     * @return IAttribute
     */
    public function getAttribute(IDependencyPool $pool, string $type, $value) : IAttribute
    {
        switch ($type) {
            case CourseAttribute::class:
                return $this->course_factory->getAttribute($pool, $value);

            case GroupAttribute::class:
                return $this->group_factory->getAttribute($pool, $value);

            case StaticAttribute::class:
                return $this->static_factory->getAttribute($value);

            default:
                return new NullAttribute($value);
        }
    }
}