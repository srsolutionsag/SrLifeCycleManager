<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Form\FormInputBuilder;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\Refinery\Factory as Refinery;
use ilPlugin;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AttributeInputBuilder extends FormInputBuilder
{
    public const KEY_ATTRIBUTE_TYPE  = 'attribute_value_type';
    public const KEY_ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * @var AttributeFactory;
     */
    protected $attribute_factory;

    /**
     * @inheritDoc
     */
    public function __construct(AttributeFactory $attribute_factory, InputFactory $input_factory, Refinery $refinery, ilPlugin $plugin)
    {
        parent::__construct($input_factory, $refinery, $plugin);

        $this->attribute_factory = $attribute_factory;
    }

    /**
     * @return array<string, string>
     */
    protected function getAttributeOptions(array $attribute_list) : array
    {
        $options = [];
        foreach ($attribute_list as $attribute) {
            $options[$attribute] = $this->translate($attribute);
        }

        return $options;
    }
}