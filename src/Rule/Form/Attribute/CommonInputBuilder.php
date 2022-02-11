<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonInputBuilder extends AttributeInputBuilder
{
    /**
     * @inheritDoc
     */
    public function getInputName() : string
    {
        return CommonAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getInput() : Input
    {
        return $this->input_factory->group([
            
            // select input for common value types
            self::KEY_ATTRIBUTE_TYPE => $this->input_factory->select(
                $this->translate('common_attribute_type'),
                $this->getAttributeOptions(
                    $this->attribute_factory->common()->getAttributeList()
                )
            ),

            // text input for common values
            self::KEY_ATTRIBUTE_VALUE => $this->input_factory->text($this->translate('common_attribute_value'))

        ], $this->plugin->txt('common_attribute'));
    }
}