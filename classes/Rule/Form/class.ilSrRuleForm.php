<?php

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\Form\RuleFormBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Form\Attribute\AttributeInputBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRuleForm extends ilSrAbstractMainForm
{
    /**
     * @var IRoutine
     */
    private $routine;

    /**
     * ilSrRuleForm constructor
     *
     * @param UIServices                     $ui
     * @param ilCtrl                         $ctrl
     * @param Factory                        $refinery
     * @param ilSrLifeCycleManagerPlugin     $plugin
     * @param ilSrLifeCycleManagerRepository $repository
     * @param IRoutine                       $routine
     */
    public function __construct(
        UIServices $ui,
        ilCtrl $ctrl,
        Factory $refinery,
        ilSrLifeCycleManagerPlugin $plugin,
        ilSrLifeCycleManagerRepository $repository,
        IRoutine $routine
    ) {
        $this->ui         = $ui;
        $this->ctrl       = $ctrl;
        $this->inputs     = $ui->factory()->input()->field();
        $this->refinery   = $refinery;
        $this->plugin     = $plugin;
        $this->repository = $repository;
        $this->routine    = $routine;

        $form_builder = new RuleFormBuilder(
            new AttributeFactory(),
            $ui->factory()->input()->field(),
            $ui->factory()->input()->container()->form(),
            $this->refinery,
            $this->plugin
        );

        $this->form = $form_builder->getForm(
            $this->getFormAction()
        );
    }

    /**
     * @inheritDoc
     */
    protected function getFormAction() : string
    {
        $this->ctrl->setParameterByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::QUERY_PARAM_ROUTINE_ID,
            $this->routine->getId()
        );

        return $this->ctrl->getFormActionByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::CMD_RULE_SAVE
        );
    }

    /**
     * @inheritDoc
     */
    protected function getFormInputs() : array
    {
        throw new LogicException("This method should not be invoked.");
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

        if (!empty($form_data[RuleFormBuilder::KEY_LHS_VALUE][RuleFormBuilder::INDEX_ATTRIBUTE_TYPE]) &&
            CommonAttribute::class === $form_data[RuleFormBuilder::KEY_LHS_VALUE][RuleFormBuilder::INDEX_ATTRIBUTE_TYPE] &&
            empty($form_data[RuleFormBuilder::KEY_LHS_VALUE][RuleFormBuilder::INDEX_ATTRIBUTE_VALUE][AttributeInputBuilder::KEY_ATTRIBUTE_TYPE])
        ) {

        }

        $attribute_type_index  = 0;
        $attribute_value_index = 1;

        if (empty($form_data[RuleFormBuilder::KEY_LHS_VALUE][$attribute_type_index]) ||
            empty($form_data[RuleFormBuilder::KEY_LHS_VALUE][$attribute_value_index][AttributeInputBuilder::KEY_ATTRIBUTE_VALUE])
        ) {

        }

        $x = 1;
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        $x = 1;
    }
}