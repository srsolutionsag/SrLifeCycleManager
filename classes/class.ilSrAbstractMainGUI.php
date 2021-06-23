<?php

/**
 * Class ilSrAbstractMainGUI provides derived GUI classes with common
 * functionalities and dependencies.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class CAN be extended by further GUI classes of this plugin that
 * share (at least) dependencies initialised in the constructor.
 * It also provides some basic methods that can be used to display or send
 * some info messages for example.
 *
 * A noticeable structural design maybe is that each derived GUI class at
 * least implements the abstract index method. This way GUI classes are
 * a bit more consistent, and redirects to a(nother) GUI is always made
 * the same way.
 *
 * @see ilSrAbstractMainGUI::redirect(), ilSrAbstractMainGUI::cancel()
 */
abstract class ilSrAbstractMainGUI
{
    /**
     * @var string command/method name every derived class must implement.
     */
    public const CMD_INDEX = 'index';

    /**
     * ilSrAbstractMainGUI common lang vars
     */
    protected const MSG_PERMISSION_DENIED   = 'msg_permission_denied';
    protected const MSG_OBJECT_NOT_FOUND    = 'msg_object_not_found';
    protected const MSG_BACK_TO             = 'msg_back_to';

    /**
     * ilSrAbstractMainGUI configuration tabs (used as id and lang var).
     */
    protected const TAB_CONFIG_INDEX  = 'tab_config_index';
    protected const TAB_ROUTINE_INDEX = 'tab_routine_index';

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
     * @var ilTabsGUI
     */
    protected $tabs;

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

        $this->ui       = $DIC->ui();
        $this->http     = $DIC->http();
        $this->ctrl     = $DIC->ctrl();
        $this->user     = $DIC->user();
        $this->tabs     = $DIC->tabs();

        $this->plugin = ilSrLifeCycleManagerPlugin::getInstance();
        $this->repository = ilSrLifeCycleManagerRepository::getInstance();
    }

    /**
     * This method dispatches ilCtrl's current command.
     *
     * Derived classes of this GUI are expected to be the last command-
     * class in the control flow, and must therefore dispatch ilCtrl's
     * current command.
     *
     * It may occur that a further command-class is needed, but in this
     * case a redirect via @see ilSrAbstractMainGUI::redirect()
     * should be made.
     */
    abstract public function executeCommand() : void;

    /**
     * This method is the entry point of the command class.
     *
     * Derived GUI classes should at least have an index method, because
     * else they should not be a GUI class.
     *
     * This also makes the code more consistent, as redirects to other
     * GUI classes can always be made the same.
     */
    abstract protected function index() : void;

    /**
     * Adds the configuration tabs of this plugin to the current page.
     * The provided tab id will be used to activate a tab.
     *
     * @param string|null $active_tab_id
     */
    protected function addConfigurationTabs(string $active_tab_id = null) : void
    {
        // add plugin-configuration tab only for administrator
        if (ilSrAccess::isUserAdministrator($this->user->getId())) {
            $this->tabs->addTab(
                self::TAB_CONFIG_INDEX,
                $this->plugin->txt(self::TAB_CONFIG_INDEX),
                $this->ctrl->getLinkTargetByClass(
                    ilSrConfigGUI::class,
                    self::CMD_INDEX
                )
            );
        }

        $this->tabs->addTab(
            self::TAB_ROUTINE_INDEX,
            $this->plugin->txt(self::TAB_ROUTINE_INDEX),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                self::CMD_INDEX
            )
        );

        if (null !== $active_tab_id) {
            $this->tabs->activateTab($active_tab_id);
        }
    }

    /**
     * Redirects to another command class with the given name.
     *
     * @param string $class_name
     */
    protected function redirect(string $class_name) : void
    {
        $this->ctrl->redirectByClass(
            $class_name,
            self::CMD_INDEX
        );
    }

    /**
     * Always redirects to the command classes index method.
     *
     * @see ilSrAbstractMainGUI::index()
     */
    protected function cancel() : void
    {
        $this->ctrl->redirectByClass(
            static::class,
            self::CMD_INDEX
        );
    }

    /**
     * overrides the current back-to tab entry with a custom target.
     *
     * @param string $link
     */
    protected function overrideBack2Target(string $link) : void
    {
        $this->tabs->setBack2Target($this->plugin->txt(self::MSG_BACK_TO), $link);
    }

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