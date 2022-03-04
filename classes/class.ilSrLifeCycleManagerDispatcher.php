<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Handles all requests of this plugin and dispatches them to the responsible class.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerConfigGUI
 *
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrConfigGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrNotificationGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRoutineGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRuleGUI
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerDispatcher
{
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $global_template;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Initializes the global template and ilCtrl.
     */
    public function __construct()
    {
        global $DIC;
        $this->global_template = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Dispatches all plugin requests to the executing command class.
     *
     * Whenever a new command class is added the PHPDoc comments above
     * must be complemented by an according '@ilCtrl_Calls' statement.
     *
     * @throws LogicException if the command class could not be found
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilSrConfigGUI::class):
                $this->safelyForward(ilSrConfigGUI::class);
                break;
            case strtolower(ilSrNotificationGUI::class):
                $this->safelyForward(ilSrNotificationGUI::class);
                break;
            case strtolower(ilSrRoutineGUI::class):
                $this->safelyForward(ilSrRoutineGUI::class);
                break;
            case strtolower(ilSrRuleGUI::class):
                $this->safelyForward(ilSrRuleGUI::class);
                break;

            default:
                throw new LogicException(self::class . " MUST never be the executing class.");
        }

        // if requests have other classes than the ilAdministrationGUI as
        // baseclass, the global template must be printed manually.
        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION !== self::getOriginType()) {
            $this->global_template->printToStdout();
        }
    }

    /**
     * Returns the origin-type of the current request.
     *
     * The origin is determined by ilCtrl's call-history, whereas the
     * current baseclass is crucial. The plugin will currently distinguish
     * between the administration and the repository. External origins
     * are not considered here.
     *
     * @return int
     */
    public static function getOriginType() : int
    {
        global $DIC;

        $call_history = $DIC->ctrl()->getCallHistory();
        $base_class   = array_shift($call_history);
        $base_class   = strtolower($base_class['class']);

        switch ($base_class) {
            // because (somehow) this class cannot be called by ilRepositoryGUI,
            // all requests from there will be handled via ilUIPluginRouterGUI.
            case strtolower(ilUIPluginRouterGUI::class):
                return IRoutine::ORIGIN_TYPE_REPOSITORY;

            case strtolower(ilAdministrationGUI::class):
                return IRoutine::ORIGIN_TYPE_ADMINISTRATION;

            default:
                return IRoutine::ORIGIN_TYPE_UNKNOWN;
        }
    }

    /**
     * Returns a fully qualified link target for the given class and command.
     *
     * This method can be used whenever a link to a command class of this plugin
     * is made from outside ilCtrl's current scope (e.g. MenuProvider)
     *
     * @param string $class
     * @param string $cmd
     * @return string
     */
    public static function getLinkTarget(string $class, string $cmd) : string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class, $class],
            $cmd
        );
    }

    /**
     * Safely forwards the current request to the given command class.
     *
     * Since this plugin implements GUI classes, that aren't working if certain
     * required GET parameters are missing, they might throw an according
     * LogicException. This method therefore wraps the mechanism and catches
     * possible exceptions to display an on-screen message instead.
     *
     * @param string $class_name
     */
    protected function safelyForward(string $class_name) : void
    {
        try {
            $this->ctrl->forwardCommand(new $class_name());
        } catch (LogicException|ilCtrlException $exception) {
            $this->global_template->setOnScreenMessage('failure', $exception->getMessage());
        }
    }
}