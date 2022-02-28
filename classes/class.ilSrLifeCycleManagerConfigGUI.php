<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * This is the entry point of the plugin-configuration.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The classes only purpose is, to forward requests to the configuration
 * to the actual implementation: @see ilSrConfigGUI.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerConfigGUI extends ilPluginConfigGUI
{
    /**
     * Forwards the request to @see ilSrConfigGUI and sets the
     * corresponding command.
     *
     * @param string $cmd
     * @throws ilCtrlException
     */
    public function performCommand($cmd) : void
    {
        global $DIC;

        $DIC->ctrl()->setCmd(ilSrConfigGUI::CMD_INDEX);
        $DIC->ctrl()->setCmdClass(ilSrConfigGUI::class);
        $DIC->ctrl()->forwardCommand(new ilSrConfigGUI());
    }
}