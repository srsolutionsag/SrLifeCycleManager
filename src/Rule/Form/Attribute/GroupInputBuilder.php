<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAttribute;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupInputBuilder extends AttributeInputBuilder
{
    /**
     * @inheritDoc
     */
    public function getInputName() : string
    {
        return GroupAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getInput() : Input
    {
        return $this->input_factory->group([

            self::KEY_ATTRIBUTE_VALUE => $this->input_factory->select(
                $this->translate('group_attribute_value'),
                $this->getAttributeOptions(
                    $this->attribute_factory->group()->getAttributeList()
                )
            ),

        ], $this->plugin->txt('group_attribute'));
    }
}