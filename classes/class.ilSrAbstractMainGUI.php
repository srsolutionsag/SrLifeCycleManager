<?php

/**
 * Class ilSrAbstractMainGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilSrAbstractMainGUI
{
    /**
     * ilSrAbstractMainGUI common lang vars
     */
    protected const MSG_PERMISSION_DENIED   = 'msg_permission_denied';
    protected const MSG_OBJECT_NOT_FOUND    = 'msg_object_not_found';

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * ilSrAbstractMainGUI constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->plugin = ilSrLifeCycleManagerPlugin::getInstance();
        $this->repository = ilSrLifeCycleManagerRepository::getInstance();
    }

    /**
     * this method MUST dispatch ilCtrl's current command
     */
    abstract public function executeCommand() : void;

    /**
     * displays an error message for given lang-var on the next page (redirect).
     *
     * @param string $lang_var
     */
    protected function sendErrorMessage(string $lang_var) : void
    {
        ilUtil::sendFailure($this->plugin->txt($lang_var), true);
    }

    /**
     * displays an error message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displayErrorMessage(string $lang_var) : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render(
                $this->ui->factory()->messageBox()->failure(
                    $this->plugin->txt($lang_var)
                )
            )
        );
    }

    /**
     * displays a success message for given lang-var on the next page (redirect).
     *
     * @param string $lang_var
     */
    protected function sendSuccessMessage(string $lang_var) : void
    {
        ilUtil::sendSuccess($this->plugin->txt($lang_var), true);
    }

    /**
     * displays an success message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displaySuccessMessage(string $lang_var) : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render(
                $this->ui->factory()->messageBox()->success(
                    $this->plugin->txt($lang_var)
                )
            )
        );
    }

    /**
     * displays an info message for given lang-var on the next page (redirect).
     *
     * @param string $lang_var
     */
    protected function sendInfoMessage(string $lang_var) : void
    {
        ilUtil::sendInfo($this->plugin->txt($lang_var), true);
    }

    /**
     * displays an info message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displayInfoMessage(string $lang_var) : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render(
                $this->ui->factory()->messageBox()->info(
                    $this->plugin->txt($lang_var)
                )
            )
        );
    }
}