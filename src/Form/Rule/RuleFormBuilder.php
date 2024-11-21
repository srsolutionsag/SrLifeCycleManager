<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant\ParticipantAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormBuilder extends AbstractFormBuilder
{
    // RuleFormBuilder input keys:
    public const KEY_LHS_VALUE = 'lhs_value';
    public const KEY_RHS_VALUE = 'rhs_value';
    public const KEY_OPERATOR = 'operator';
    public const KEY_ATTR_TYPE = 'attribute_type';
    public const KEY_ATTR_VALUE = 'attribute_value';

    // RuleFormBuilder group indexes:
    public const INDEX_GROUP_TYPE = 0;
    public const INDEX_GROUP_VALUE = 1;

    /**
     * @var Input[]
     */
    protected $attribute_inputs = [];

    /**
     * @var string[]
     */
    protected $common_attribute_list;

    /**
     * @var AttributeFactory
     */
    protected $attributes;

    /**
     * @var IRule|null
     */
    protected $rule;

    /**
     * @param ITranslator      $translator
     * @param FormFactory      $forms
     * @param FieldFactory     $fields
     * @param Refinery         $refinery
     * @param AttributeFactory $attributes
     * @param IRule            $rule
     * @param string           $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        AttributeFactory $attributes,
        IRule $rule,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->common_attribute_list = $attributes->getAttributeValues(CommonAttribute::class);
        $this->attributes = $attributes;
        $this->rule = $rule;
    }

    /**
     * @inheritDoc
     */
    public function getForm(): UIForm
    {
        $inputs[self::KEY_LHS_VALUE] = $this->getSwitchableAttributesInput(self::KEY_LHS_VALUE);

        if (null !== ($lhs_value = $this->getCurrentValueBySide(IRule::RULE_SIDE_LEFT))) {
            $inputs[self::KEY_LHS_VALUE] = $inputs[self::KEY_LHS_VALUE]->withValue($lhs_value);
        }

        $inputs[self::KEY_RHS_VALUE] = $this->getSwitchableAttributesInput(self::KEY_RHS_VALUE);

        if (null !== ($rhs_value = $this->getCurrentValueBySide(IRule::RULE_SIDE_RIGHT))) {
            $inputs[self::KEY_RHS_VALUE] = $inputs[self::KEY_RHS_VALUE]->withValue($rhs_value);
        }

        $inputs[self::KEY_OPERATOR] = $this
            ->getOperatorInput()
            ->withValue(
                (null !== $this->rule->getRuleId()) ? $this->rule->getOperator() : null
            );

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }

    /**
     * Adds common-attribute inputs to the current form.
     *
     * @return self
     */
    public function addCommonAttributes(): self
    {
        $this->attribute_inputs[CommonAttribute::class] = $this->getCommonAttributeInput();
        return $this;
    }

    /**
     * Adds course-attribute inputs to the current form.
     *
     * @return self
     */
    public function addCourseAttributes(): self
    {
        $this->attribute_inputs[CourseAttribute::class] = $this->getDynamicAttributeInput(CourseAttribute::class);
        return $this;
    }

    /**
     * Adds object-attribute inputs to the current form.
     *
     * @return self
     */
    public function addObjectAttributes(): self
    {
        $this->attribute_inputs[ObjectAttribute::class] = $this->getDynamicAttributeInput(ObjectAttribute::class);
        return $this;
    }

    /**
     * Adds participant-attribute inputs to the current form.
     *
     * @return self
     */
    public function addParticipantAttributes(): self
    {
        $this->attribute_inputs[ParticipantAttribute::class] = $this->getDynamicAttributeInput(
            ParticipantAttribute::class
        );
        return $this;
    }

    /**
     * Adds survey-attribute inputs to the current form.
     *
     * @return self
     */
    public function addSurveyAttributes(): self
    {
        $this->attribute_inputs[SurveyAttribute::class] = $this->getDynamicAttributeInput(
            SurveyAttribute::class
        );
        return $this;
    }

    /**
     * @return Input
     */
    protected function getOperatorInput(): Input
    {
        return $this->fields
            ->select(
                $this->translator->txt(self::KEY_OPERATOR),
                [
                    IRule::OPERATOR_EQUAL => $this->translator->txt(IRule::OPERATOR_EQUAL),
                    IRule::OPERATOR_NOT_EQUAL => $this->translator->txt(IRule::OPERATOR_NOT_EQUAL),
                    IRule::OPERATOR_GREATER => $this->translator->txt(IRule::OPERATOR_GREATER),
                    IRule::OPERATOR_GREATER_EQUAL => $this->translator->txt(IRule::OPERATOR_GREATER_EQUAL),
                    IRule::OPERATOR_LESSER => $this->translator->txt(IRule::OPERATOR_LESSER),
                    IRule::OPERATOR_LESSER_EQUAL => $this->translator->txt(IRule::OPERATOR_LESSER_EQUAL),
                    IRule::OPERATOR_CONTAINS => $this->translator->txt(IRule::OPERATOR_CONTAINS),
                    IRule::OPERATOR_IN_ARRAY => $this->translator->txt(IRule::OPERATOR_IN_ARRAY),
                ]
            )->withRequired(true);
    }

    /**
     * @return Group
     */
    protected function getCommonAttributeInput(): Group
    {
        $inputs[self::KEY_ATTR_TYPE] = $this->fields
            ->select(
                $this->translator->txt(CommonAttribute::class),
                $this->getAttributeOptions(
                    $this->attributes->getAttributeValues(CommonAttribute::class)
                )
            )->withRequired(true);

        $inputs[self::KEY_ATTR_VALUE] = $this->fields
            ->text($this->translator->txt('common_attribute_value'));

        return $this->fields
            ->group($inputs, $this->translator->txt(CommonAttribute::class));
    }

    /**
     * @param string $attribute_type (course|group)
     * @return Group
     */
    protected function getDynamicAttributeInput(string $attribute_type): Group
    {
        $inputs[self::KEY_ATTR_VALUE] = $this->fields
            ->select(
                $this->translator->txt($attribute_type),
                $this->getAttributeOptions(
                    $this->attributes->getAttributeValues($attribute_type)
                )
            )->withRequired(true);

        return $this->fields
            ->group(
                $inputs,
                $this->translator->txt($attribute_type)
            );
    }

    /**
     * @param string $lang_var
     * @return SwitchableGroup
     */
    protected function getSwitchableAttributesInput(string $lang_var): SwitchableGroup
    {
        return $this->fields
            ->switchableGroup(
                $this->attribute_inputs,
                $this->translator->txt($lang_var)
            );
    }

    /**
     * @return array<string, string>
     */
    protected function getAttributeOptions(array $attribute_list): array
    {
        $options = [];
        foreach ($attribute_list as $attribute) {
            $options[$attribute] = $this->translator->txt($attribute);
        }

        return $options;
    }

    /**
     * Returns the current "withValue()" argument for switchable-groups.
     *
     * @param string $rule_side (lhs|rhs)
     * @return array<string, string|array<string, string>>|null
     */
    protected function getCurrentValueBySide(string $rule_side): ?array
    {
        $attribute_type = $this->rule->getTypeBySide($rule_side);
        if (empty($attribute_type)) {
            return null;
        }

        // common attributes are mapped to the CommonAttribute array key,
        // they specify the actual attribute type within another sub-input.
        if ($this->isCommonAttribute($attribute_type)) {
            return [
                CommonAttribute::class, // must always be the same
                [
                    self::KEY_ATTR_TYPE => $attribute_type,
                    self::KEY_ATTR_VALUE => $this->rule->getValueBySide($rule_side)
                ]
            ];
        }

        // dynamic attributes are directly mapped to their type
        return [
            $attribute_type,
            [
                self::KEY_ATTR_VALUE => $this->rule->getValueBySide($rule_side)
            ]
        ];
    }

    /**
     * @param string $attribute_type
     * @return bool
     */
    protected function isCommonAttribute(string $attribute_type): bool
    {
        return in_array($attribute_type, $this->common_attribute_list, true);
    }
}
