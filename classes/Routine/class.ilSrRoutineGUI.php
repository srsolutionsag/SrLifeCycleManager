<?php

/**
 * Class ilSrRoutineGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineGUI extends ilSrAbstractMainGUI
{
    /**
     * ilSrRoutineGUI command names (methods)
     */
    public const CMD_ROUTINE_INDEX = 'index';

    /**
     * @inheritDoc
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_ROUTINE_INDEX);
        switch ($cmd) {
            case self::CMD_ROUTINE_INDEX:
                if (ilSrAccess::canUserDoStuff($this->user->getId())) {
                    // execute command if user access is granted
                    $this->{$cmd}();
                } else {
                    $this->sendErrorMessage(self::MSG_PERMISSION_DENIED);
                }
                break;

            default:
                $this->sendErrorMessage(self::MSG_OBJECT_NOT_FOUND);
                break;
        }
    }

    public function index() : void
    {
        echo "hello world";
    }
}