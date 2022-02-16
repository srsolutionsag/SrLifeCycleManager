<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Builder\Form\Rule\Attribute\AttributeInputBuilder;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Rule\RuleFormBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRuleForm extends ilSrAbstractForm
{
    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilGlobalTemplateInterface      $global_template
     * @param Renderer                       $renderer
     * @param Form                           $form
     * @param IRoutine                       $routine
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilGlobalTemplateInterface $global_template,
        Renderer $renderer,
        Form $form,
        IRoutine $routine
    ) {
        parent::__construct($repository, $global_template, $renderer, $form);

        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // store form-data indexes in variables for readability.
        $lhs_value = RuleFormBuilder::KEY_LHS_VALUE;
        $rhs_value = RuleFormBuilder::KEY_RHS_VALUE;
        $group_type = RuleFormBuilder::INDEX_ATTRIBUTE_TYPE;
        $group_content = RuleFormBuilder::INDEX_ATTRIBUTE_VALUE;
        $attr_type = AttributeInputBuilder::KEY_ATTRIBUTE_TYPE;
        $attr_value = AttributeInputBuilder::KEY_ATTRIBUTE_VALUE;

        // validate that the LHS value type is not empty for all other types
        // than CommonNull.
        if (CommonAttribute::class === $form_data[$lhs_value][$group_type] &&
            CommonNull::class !== $form_data[$lhs_value][$group_content][$attr_type] &&
            empty($form_data[$lhs_value][$group_content][$attr_value])
        ) {
            return false;
        }

        // validate that the RHS value type is not empty for all other types
        // than CommonNull.
        if (CommonAttribute::class === $form_data[$rhs_value][$group_type] &&
            CommonNull::class !== $form_data[$rhs_value][$group_content][$attr_type] &&
            empty($form_data[$rhs_value][$group_content][$attr_value])
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        // store form-data indexes in variables for readability.
        $key_lhs_value = RuleFormBuilder::KEY_LHS_VALUE;
        $key_rhs_value = RuleFormBuilder::KEY_RHS_VALUE;
        $key_group_type = RuleFormBuilder::INDEX_ATTRIBUTE_TYPE;
        $key_group_content = RuleFormBuilder::INDEX_ATTRIBUTE_VALUE;
        $key_attr_type = AttributeInputBuilder::KEY_ATTRIBUTE_TYPE;
        $key_attr_value = AttributeInputBuilder::KEY_ATTRIBUTE_VALUE;

        $lhs_value = $form_data[$key_lhs_value][$key_group_content][$key_attr_value];
        $rhs_value = $form_data[$key_rhs_value][$key_group_content][$key_attr_value];

        $lhs_type = (CommonAttribute::class === $form_data[$key_lhs_value][$key_group_type]) ?
            $form_data[$key_lhs_value][$key_group_content][$key_attr_type] :
            $form_data[$key_lhs_value][$key_group_type]
        ;

        $rhs_type = (CommonAttribute::class === $form_data[$key_rhs_value][$key_group_type]) ?
            $form_data[$key_rhs_value][$key_group_content][$key_attr_type] :
            $form_data[$key_rhs_value][$key_group_type]
        ;

        $rule = new Rule(
            null,
            $lhs_type,
            $lhs_value,
            $form_data[RuleFormBuilder::KEY_OPERATOR],
            $rhs_type,
            $rhs_value
        );

        // store the new rule and create a relation to the current routine.
        $this->repository->routine()->addRule(
            $this->routine,
            $this->repository->rule()->store($rule)
        );
    }
}