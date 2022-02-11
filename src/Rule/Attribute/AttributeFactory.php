<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course\ICourseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group\IGroupRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AttributeFactory
{
    /**
     * @var CommonAttributeFactory
     */
    protected $common_factory;

    /**
     * @var CourseAttributeFactory
     */
    protected $course_factory;

    /**
     * @var GroupAttributeFactory
     */
    protected $group_factory;

    /**
     * AttributeFactory constructor
     */
    public function __construct()
    {
        $this->common_factory = new CommonAttributeFactory();
        $this->course_factory = new CourseAttributeFactory();
        $this->group_factory = new GroupAttributeFactory();
    }

    /**
     * @param IRequirement $requirement
     * @param string       $type
     * @param mixed        $value
     * @return IAttribute
     */
    public function getAttribute(IRequirement $requirement, string $type, $value) : IAttribute
    {
        switch ($type) {
            case CourseAttribute::class:
                /** @var $requirement ICourseRequirement */
                return $this->course_factory->getAttribute($requirement, $value);

            case GroupAttribute::class:
                /** @var $requirement IGroupRequirement */
                return $this->group_factory->getAttribute($requirement, $value);

            default:
                return $this->common_factory->getAttribute($type, $value);
        }
    }

    /**
     * @return CourseAttributeFactory
     */
    public function course() : CourseAttributeFactory
    {
        return $this->course_factory;
    }

    /**
     * @return GroupAttributeFactory
     */
    public function group() : GroupAttributeFactory
    {
        return $this->group_factory;
    }

    /**
     * @return CommonAttributeFactory
     */
    public function common() : CommonAttributeFactory
    {
        return $this->common_factory;
    }
}