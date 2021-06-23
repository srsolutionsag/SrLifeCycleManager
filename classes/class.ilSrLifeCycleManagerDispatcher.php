<?php

/**
 * Class ilSrLifeCycleManagerDispatcher is responsible for ALL plugins requests.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerConfigGUI
 *
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrConfigGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrNotificationGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRoutineGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRuleGUI
 */
final class ilSrLifeCycleManagerDispatcher
{
    /**
     * Dispatches all plugin requests to the executing command class.
     *
     * Whenever a new command class is added the PHPDoc comments above
     * must be complemented by an according '@ilCtrl_Calls' statement.
     *
     * @throws ilCtrlException if the command class could not be loaded
     * @throws LogicException if the command class could not be found
     */
    public function executeCommand() : void
    {
        global $DIC;

        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilSrConfigGUI::class):
                $DIC->ctrl()->forwardCommand(new ilSrConfigGUI());
                break;
            case strtolower(ilSrNotificationGUI::class):
                $DIC->ctrl()->forwardCommand(new ilSrNotificationGUI());
                break;
            case strtolower(ilSrRoutineGUI::class):
                $DIC->ctrl()->forwardCommand(new ilSrRoutineGUI());
                break;
            case strtolower(ilSrRuleGUI::class):
                $DIC->ctrl()->forwardCommand(new ilSrRuleGUI());
                break;

            default:
                throw new LogicException(self::class . " MUST never be executing class.");
        }

        // in case requests are coming from ilUIPluginRouterGUI we need to
        // print the template manually (doesn't matter if it's repeatedly).
        $DIC->ui()->mainTemplate()->printToStdout();
    }

    /**
     * Returns a fully qualified link target for the given class and command.
     *
     * This method can be used whenever a link to a command class of this plugin
     * is made from outside of ilCtrl's current scope (e.g. MenuProvider)
     *
     * @param string $class
     * @param string $cmd
     * @return string
     */
    public static function buildFullyQualifiedLinkTarget(string $class, string $cmd) : string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class, $class],
            $cmd
        );
    }
}