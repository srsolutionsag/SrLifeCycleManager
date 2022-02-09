<?php

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

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
        // dependencies MUST be added before the parent
        // constructor is called, as they are already by it.
        $this->routine = $routine;

        parent::__construct($ui, $ctrl, $refinery, $plugin, $repository);
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
        $inputs = [];

        $inputs[ilSrRule::F_LHS_TYPE] =

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // TODO: Implement validateFormData() method.
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        // TODO: Implement handleFormData() method.
    }
}