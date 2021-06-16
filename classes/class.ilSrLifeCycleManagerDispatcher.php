<?php

/**
 * Class ilSrLifeCycleManagerDispatcher is the plugin entry point.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerConfigGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrMenuProvider
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrToolProvider
 *
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrConfigGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrNotificationGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRoutineGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRuleGUI
 */
final class ilSrLifeCycleManagerDispatcher
{
    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * ilSrLifeCycleManagerDispatcher constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    /**
     * dispatches ilCtrl's 'next_class' and forwards the command.
     *
     * @throws ilCtrlException if ilCtrl's next-class wasn't found
     * @throws LogicException if ilCtrl's next-class is $this
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilSrConfigGUI::class):
                $this->ctrl->forwardCommand(new ilSrConfigGUI());
                break;
            case strtolower(ilSrNotificationGUI::class):
                $this->ctrl->forwardCommand(new ilSrNotificationGUI());
                break;
            case strtolower(ilSrRoutineGUI::class):
                $this->ctrl->forwardCommand(new ilSrRoutineGUI());
                break;
            case strtolower(ilSrRuleGUI::class):
                $this->ctrl->forwardCommand(new ilSrRuleGUI());
                break;

            default:
                throw new LogicException(self::class . " MUST never be executing class.");
        }
    }
}