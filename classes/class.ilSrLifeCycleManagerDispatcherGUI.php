<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * Handles all requests of this plugin and dispatches them to the responsible class.
 *
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcherGUI: ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcherGUI: ilSrLifeCycleManagerConfigGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcherGUI: ilObjComponentSettingsGUI
 *
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrConfigGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrRoutineAssignmentGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrObjectAssignmentGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrConfirmationGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrReminderGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrRoutineGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrRuleGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrRoutinePreviewGUI
 * @ilCtrl_Calls      ilSrLifeCycleManagerDispatcherGUI: ilSrWhitelistGUI
 *
 * @noinspection      AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerDispatcherGUI
{
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $global_template;

    protected IConfig $config;

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
        $this->config = (new ilSrConfigRepository($DIC->database(), $DIC->rbac()))->get();
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
    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilSrConfigGUI::class):
                $this->safelyForward(ilSrConfigGUI::class);
                break;
            case strtolower(ilSrRoutineAssignmentGUI::class):
                $this->safelyForward(ilSrRoutineAssignmentGUI::class);
                break;
            case strtolower(ilSrObjectAssignmentGUI::class):
                $this->safelyForward(ilSrObjectAssignmentGUI::class);
                break;
            case strtolower(ilSrConfirmationGUI::class):
                $this->safelyForward(ilSrConfirmationGUI::class);
                break;
            case strtolower(ilSrReminderGUI::class):
                $this->safelyForward(ilSrReminderGUI::class);
                break;
            case strtolower(ilSrRoutineGUI::class):
                $this->safelyForward(ilSrRoutineGUI::class);
                break;
            case strtolower(ilSrRuleGUI::class):
                $this->safelyForward(ilSrRuleGUI::class);
                break;
            case strtolower(ilSrWhitelistGUI::class):
                $this->safelyForward(ilSrWhitelistGUI::class);
                break;
            case strtolower(ilSrRoutinePreviewGUI::class):
                $this->safelyForward(ilSrRoutinePreviewGUI::class);
                break;

            case strtolower(self::class):
                throw new LogicException(self::class . " MUST never be the executing class.");

            default:
                throw new LogicException(self::class . " is not a known command class.");
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
    public static function getOriginType(): int
    {
        global $DIC;

        $call_history = $DIC->ctrl()->getCallHistory();
        $base_class = array_shift($call_history);
        $base_class = strtolower((string) $base_class[ilCtrlInterface::PARAM_CMD_CLASS]);

        return match ($base_class) {
            strtolower(ilUIPluginRouterGUI::class) => IRoutine::ORIGIN_TYPE_REPOSITORY,
            strtolower(ilAdministrationGUI::class) => IRoutine::ORIGIN_TYPE_ADMINISTRATION,
            default => IRoutine::ORIGIN_TYPE_UNKNOWN,
        };
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
    public static function getLinkTarget(string $class, string $cmd): string
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
    protected function safelyForward(string $class_name): void
    {
        try {
            $this->ctrl->forwardCommand(new $class_name());
        } catch (LogicException|ilCtrlException $exception) {
            $this->global_template->setOnScreenMessage(
                'failure',
                ($this->config->isDebugModeEnabled()) ?
                    $this->getExceptionString($exception) :
                    $exception->getMessage()
            );
        }
    }

    /**
     * Helper function to nicely format the exception message to display on screen.
     *
     * @param Throwable $exception
     * @return string
     */
    protected function getExceptionString(Throwable $exception): string
    {
        $message = "{$exception->getMessage()} : ";
        $message .= "<br /><br />";

        return $message . str_replace(
            PHP_EOL,
            "<br />",
            $exception->getTraceAsString()
        );
    }
}
