<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form\Rule\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\Refinery\Factory as Refinery;
use ilPlugin;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AttributeBuilderFactory
{
    /**
     * @var CourseInputBuilder
     */
    protected $course_builder;

    /**
     * @var GroupInputBuilder
     */
    protected $group_builder;

    /**
     * @var CommonInputBuilder
     */
    protected $common_builder;

    /**
     * @param AttributeFactory $attribute_factory
     * @param InputFactory     $input_factory
     * @param Refinery         $refinery
     * @param ilPlugin         $plugin
     */
    public function __construct(
        AttributeFactory $attribute_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ilPlugin $plugin
    ) {
        $this->course_builder = new CourseInputBuilder($attribute_factory, $input_factory, $refinery, $plugin);
        $this->group_builder = new GroupInputBuilder($attribute_factory, $input_factory, $refinery, $plugin);
        $this->common_builder = new CommonInputBuilder($attribute_factory, $input_factory, $refinery, $plugin);
    }

    /**
     * @return CourseInputBuilder
     */
    public function course() : CourseInputBuilder
    {
        return $this->course_builder;
    }

    /**
     * @return GroupInputBuilder
     */
    public function group() : GroupInputBuilder
    {
        return $this->group_builder;
    }

    /**
     * @return CommonInputBuilder
     */
    public function common() : CommonInputBuilder
    {
        return $this->common_builder;
    }
}