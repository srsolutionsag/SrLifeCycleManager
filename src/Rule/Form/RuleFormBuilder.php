<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form;

use srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute\AttributeBuilderFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;
use ilPlugin;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormBuilder extends FormBuilder
{
    public const KEY_LHS_VALUE = 'lhs_value';
    public const KEY_RHS_VALUE = 'rhs_value';
    public const KEY_OPERATOR  = 'operator';

    public const INDEX_ATTRIBUTE_VALUE = 1;
    public const INDEX_ATTRIBUTE_TYPE  = 0;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var AttributeBuilderFactory
     */
    protected $input_builders;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        AttributeFactory $attribute_factory,
        InputFactory $input_factory,
        FormFactory $form_factory,
        Refinery $refinery,
        ilPlugin $plugin
    ) {
        parent::__construct($input_factory, $form_factory, $refinery, $plugin);
        
        $this->input_builders = new AttributeBuilderFactory(
            $attribute_factory,
            $input_factory,
            $refinery,
            $plugin
        );
    }

    /**
     * @inheritDoc
     */
    public function getForm(string $form_action) : Form
    {
        return $this->form_factory->standard($form_action, [
            // switchable group for lhs attribute
            self::KEY_LHS_VALUE => $this->input_factory->switchableGroup([
                $this->input_builders->common()->getInputName() => $this->input_builders->common()->getInput(),
                $this->input_builders->course()->getInputName() => $this->input_builders->course()->getInput(),
                $this->input_builders->group()->getInputName() => $this->input_builders->group()->getInput(),
            ], $this->translate('lhs_value'))->withRequired(true),

            // switchable group for rhs attribute
            self::KEY_RHS_VALUE => $this->input_factory->switchableGroup([
                $this->input_builders->common()->getInputName() => $this->input_builders->common()->getInput(),
                $this->input_builders->course()->getInputName() => $this->input_builders->course()->getInput(),
                $this->input_builders->group()->getInputName() => $this->input_builders->group()->getInput(),
            ], $this->translate('rhs_value'))->withRequired(true),

            // select input for rule operator
            self::KEY_OPERATOR => $this->getOperatorInput()->withRequired(true),
        ]);
    }

    /**
     * @return Input
     */
    protected function getOperatorInput() : Input
    {
        return $this->input_factory->select($this->translate('operator'), [
            IRule::OPERATOR_EQUAL           => $this->translate(IRule::OPERATOR_EQUAL),
            IRule::OPERATOR_NOT_EQUAL       => $this->translate(IRule::OPERATOR_NOT_EQUAL),
            IRule::OPERATOR_GREATER         => $this->translate(IRule::OPERATOR_GREATER),
            IRule::OPERATOR_GREATER_EQUAL   => $this->translate(IRule::OPERATOR_GREATER_EQUAL),
            IRule::OPERATOR_LESSER          => $this->translate(IRule::OPERATOR_LESSER),
            IRule::OPERATOR_LESSER_EQUAL    => $this->translate(IRule::OPERATOR_LESSER_EQUAL),
            IRule::OPERATOR_CONTAINS        => $this->translate(IRule::OPERATOR_CONTAINS),
            IRule::OPERATOR_IN_ARRAY        => $this->translate(IRule::OPERATOR_IN_ARRAY),
        ]);
    }
}