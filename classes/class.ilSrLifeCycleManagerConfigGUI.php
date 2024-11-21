<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * This is the entry point of the plugin-configuration.
 *
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The classes only purpose is, to forward requests to the configuration
 * to the actual implementation: @see ilSrConfigGUI.
 *
 * @ilCtrl_IsCalledBy ilSrLifeCycleManagerConfigGUI : ilObjComponentSettingsGUI
 *
 * @noinspection      AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerConfigGUI extends ilPluginConfigGUI
{
    /**
     * Forwards the request to ilSrConfigGUI and sets the
     * corresponding command.
     *
     * @param string $cmd
     * @throws ilCtrlException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;

        if (strtolower(ilSrLifeCycleManagerDispatcherGUI::class) === $DIC->ctrl()->getNextClass($this)) {
            // forward the request to the plugin dispatcher if it's ilCtrl's
            // next command class, because this means a further command class
            // is already provided.
            $DIC->ctrl()->forwardCommand(new ilSrLifeCycleManagerDispatcherGUI());
        } else {
            // whenever ilCtrl's next class is not the plugin dispatcher the
            // request comes from ILIAS (ilAdministrationGUI) itself, in which
            // case the request is redirected to the plugins actual config GUI.
            $DIC->ctrl()->redirectByClass(
                [
                    ilAdministrationGUI::class,
                    ilObjComponentSettingsGUI::class,
                    self::class,
                    ilSrLifeCycleManagerDispatcherGUI::class,
                    ilSrConfigGUI::class
                ],
                ilSrConfigGUI::CMD_INDEX
            );
        }
    }
}
