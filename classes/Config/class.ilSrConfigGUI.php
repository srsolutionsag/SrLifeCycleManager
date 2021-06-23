<?php

/**
 * Class ilSrConfigGUI is responsible for the general plugin configuration.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class is called whenever the plugin-configuration within the
 * plugin administration is requested.
 *
 * It's responsible for general configuration and displays a form on
 * the first request. Further commands implemented in this class are
 * the change and store these configurations.
 */
final class ilSrConfigGUI extends ilSrAbstractMainGUI
{
    /**
     * ilSrConfigGUI command names (methods)
     */
    public const CMD_CONFIG_SAVE  = 'save';
    public const CMD_CONFIG_EDIT  = 'edit';

    /**
     * ilSrConfigGUI lang vars
     */
    private const MSG_CONFIGURATION_SUCCESS = 'msg_configuration_success';
    private const MSG_CONFIGURATION_ERROR   = 'msg_configuration_error';

    /**
     * @inheritDoc
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);
        switch ($cmd) {
            case self::CMD_INDEX:
            case self::CMD_CONFIG_SAVE:
            case self::CMD_CONFIG_EDIT:
                if (ilSrAccess::canUserDoStuff()) {
                    // add configuration tabs to the page and execute given command.
                    $this->addConfigurationTabs(self::TAB_CONFIG_INDEX);
                    $this->{$cmd}();
                } else {
                    $this->displayErrorMessage(self::MSG_PERMISSION_DENIED);
                }
                break;

            default:
                // displays an error message whenever a command is unknown.
                $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
        }
    }

    /**
     * Displays the general configuration form on the current page.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
    }

    /**
     * Displays the general configuration form on the current page
     * filled with the current request.
     */
    private function edit() : void
    {
        $form = $this->getForm();
    }

    /**
     * Stores the changed or added configuration to the database
     * and redirects to the index.
     */
    private function save() : void
    {
        $form = $this->getForm();
        if ($form->handleFormSubmission()) {
            $this->sendSuccessMessage(self::MSG_CONFIGURATION_SUCCESS);
        } else {
            $this->sendErrorMessage(self::MSG_CONFIGURATION_ERROR);
        }

        $this->cancel();
    }

    /**
     * Helper function that initialises the configuration form and
     * returns it.
     * @return ilSrConfigForm
     */
    private function getForm() : ilSrConfigForm
    {
        return new ilSrConfigForm($this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_CONFIG_SAVE
        ));
    }
}