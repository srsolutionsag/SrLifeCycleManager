<?php

/**
 * Class ilSrLifeCycleManagerConfigGUI is the plugin-config entry point.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrLifeCycleManagerConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var string
     */
    public const CMD_CONFIG_INDEX = 'configure';

    /**
     * @inheritDoc
     */
    public function performCommand($cmd)
    {
        global $DIC;

        if (strtolower(ilSrLifeCycleManagerDispatcher::class) === $DIC->ctrl()->getNextClass($this)) {
            $DIC->ctrl()->forwardCommand(new ilSrLifeCycleManagerDispatcher());
        } else {
            $DIC->ctrl()->redirectByClass(
                [ilSrLifeCycleManagerDispatcher::class, ilSrConfigGUI::class],
                self::CMD_CONFIG_INDEX
            );
        }
    }
}