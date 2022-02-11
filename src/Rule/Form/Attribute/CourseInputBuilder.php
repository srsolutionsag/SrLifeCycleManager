<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseInputBuilder extends AttributeInputBuilder
{
    /**
     * @inheritDoc
     */
    public function getInputName() : string
    {
        return CourseAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getInput() : Input
    {
        return $this->input_factory->group([

            self::KEY_ATTRIBUTE_VALUE => $this->input_factory->select(
                $this->translate('course_attribute_value'),
                $this->getAttributeOptions(
                    $this->attribute_factory->course()->getAttributeList()
                )
            ),

        ], $this->plugin->txt('course_attribute'));
    }
}