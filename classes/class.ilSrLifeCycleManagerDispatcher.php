<?php

/**
 * Class ilSrLifeCycleManagerDispatcher is the plugin entry point.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerToolsProvider
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerConfigGUI
 *
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : (add further GUI classes here)
 */
class ilSrLifeCycleManagerDispatcher
{
    /**
     * dispatches ilCtrl's 'next_class' and forwards the command.
     */
    public function executeCommand() : void
    {
        global $DIC;

        switch ($DIC->ctrl()->getNextClass()) {

            default:
                $this->performCommand($DIC->ctrl()->getCmd());
        }
    }

    /**
     * @param string $cmd
     */
    public function performCommand(string $cmd) : void
    {
        throw new LogicException("");
    }
}