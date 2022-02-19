<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineAwareRule;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormBuilder extends AbstractFormBuilder
{
    public const KEY_LHS_VALUE  = 'lhs_value';
    public const KEY_RHS_VALUE  = 'rhs_value';
    public const KEY_OPERATOR   = 'operator';
    public const KEY_ATTR_TYPE  = 'attribute_type';
    public const KEY_ATTR_VALUE = 'attribute_value';

    public const INDEX_GROUP_TYPE  = 0;
    public const INDEX_GROUP_VALUE = 1;

    /**
     * @var Input[]
     */
    protected $attribute_inputs = [];

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var IRoutineAwareRule|null
     */
    protected $rule;

    /**
     * @param FormFactory  $form_factory
     * @param InputFactory $input_factory
     * @param Refinery     $refinery
     * @param ITranslator  $translator
     * @param string       $form_action
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action
    ) {
        parent::__construct($form_factory, $input_factory, $refinery, $translator, $form_action);

        $this->attribute_factory = new AttributeFactory();
    }

    /**
     * @return IRoutineAwareRule
     */
    public function getRule() : IRoutineAwareRule
    {
        return $this->rule;
    }

    /**
     * @param IRoutineAwareRule $rule
     * @return $this
     */
    public function setRule(IRoutineAwareRule $rule) : self
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * @return $this
     */
    public function addCommonAttributes() : self
    {
        $this->attribute_inputs[CommonAttribute::class] = $this->getCommonAttributeInput();
        return $this;
    }

    /**
     * @return $this
     */
    public function addCourseAttributes() : self
    {
        $this->attribute_inputs[CourseAttribute::class] = $this->getDynamicAttributeInput(CourseAttribute::class);
        return $this;
    }

    /**
     * @return $this
     */
    public function addGroupAttributes() : self
    {
        $this->attribute_inputs[GroupAttribute::class] = $this->getDynamicAttributeInput(GroupAttribute::class);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getInputs() : array
    {
        return [
            self::KEY_LHS_VALUE => $this->getSwitchableAttributesInput(self::KEY_LHS_VALUE),
            self::KEY_RHS_VALUE => $this->getSwitchableAttributesInput(self::KEY_RHS_VALUE),
            self::KEY_OPERATOR => $this->getOperatorInput(),
        ];
    }

    /**
     * @return Input
     */
    protected function getOperatorInput() : Input
    {
        return $this->input_factory
            ->select(
                $this->translate(self::KEY_OPERATOR),
                [
                    IRule::OPERATOR_EQUAL           => $this->translate(IRule::OPERATOR_EQUAL),
                    IRule::OPERATOR_NOT_EQUAL       => $this->translate(IRule::OPERATOR_NOT_EQUAL),
                    IRule::OPERATOR_GREATER         => $this->translate(IRule::OPERATOR_GREATER),
                    IRule::OPERATOR_GREATER_EQUAL   => $this->translate(IRule::OPERATOR_GREATER_EQUAL),
                    IRule::OPERATOR_LESSER          => $this->translate(IRule::OPERATOR_LESSER),
                    IRule::OPERATOR_LESSER_EQUAL    => $this->translate(IRule::OPERATOR_LESSER_EQUAL),
                    IRule::OPERATOR_CONTAINS        => $this->translate(IRule::OPERATOR_CONTAINS),
                    IRule::OPERATOR_IN_ARRAY        => $this->translate(IRule::OPERATOR_IN_ARRAY),
                ]
            )->withRequired(true)
            ;
    }

    /**
     * @return Group
     */
    protected function getCommonAttributeInput() : Group
    {
        $inputs[self::KEY_ATTR_TYPE] = $this->input_factory
            ->select(
                $this->translate(CommonAttribute::class),
                $this->getAttributeOptions(
                    $this->attribute_factory->common()->getAttributeList()
                )
            )->withRequired(true)
        ;

        $inputs[self::KEY_ATTR_VALUE] = $this->input_factory
            ->text($this->translate('common_attribute_value'))
        ;

        return $this->input_factory
            ->group($inputs, $this->translate(CommonAttribute::class))
        ;
    }

    /**
     * @param string $attribute_type (course|group)
     * @return Group
     */
    protected function getDynamicAttributeInput(string $attribute_type) : Group
    {
        $factory_method = (CourseAttribute::class === $attribute_type) ? 'course' : 'group';
        $inputs[self::KEY_ATTR_VALUE] = $this->input_factory
            ->select(
                $this->translate($attribute_type),
                $this->getAttributeOptions(
                    $this->attribute_factory->{$factory_method}()->getAttributeList()
                )
            )->withRequired(true)
        ;

        return $this->input_factory
            ->group($inputs, $this->translator->txt($attribute_type)
        );
    }

    /**
     * @param string $lang_var
     * @return SwitchableGroup
     */
    protected function getSwitchableAttributesInput(string $lang_var) : SwitchableGroup
    {
        return $this->input_factory
            ->switchableGroup(
                $this->attribute_inputs,
                $this->translate($lang_var)
            )
        ;
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