<?php

/**
 * Class ilSrLifeCycleManagerConfigGUI is the plugin config entry point.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * ILIAS plugins which can be configured MUST implement this class, as it's the
 * entry point recognized by ILIAS. Neither it's location nor it's name can be
 * changed, due to the hardcoded configuration class loading in the core.
 *
 * Since the request handling of this plugin is centralized and lies within the
 * @see ilSrLifeCycleManagerDispatcher class, the only purpose of this GUI is
 * to change that entry point to another config GUI implementation.
 */
final class ilSrLifeCycleManagerConfigGUI extends ilPluginConfigGUI
{
    /**
     * This method is called whenever a request to this GUI is made and
     * redirects/forwards it to the actual config GUI implementation.
     *
     * @param string $cmd
     * @throws ilCtrlException
     */
    public function performCommand($cmd) : void
    {
        global $DIC;

        if (strtolower(ilSrLifeCycleManagerDispatcher::class) === $DIC->ctrl()->getNextClass($this)) {
            // forward the request to the plugin dispatcher if it's ilCtrl's
            // next command class, because this means a further command class
            // is already provided.
            $DIC->ctrl()->forwardCommand(new ilSrLifeCycleManagerDispatcher());
        } else {
            // whenever ilCtrl's next class is not the plugin dispatcher the
            // request comes from ILIAS (ilAdministrationGUI) itself, in which
            // case the request is redirected to the plugins actual config GUI.
            $DIC->ctrl()->redirectByClass(
                [ilSrLifeCycleManagerDispatcher::class, ilSrConfigGUI::class],
                ilSrConfigGUI::CMD_INDEX
            );
        }
    }
}