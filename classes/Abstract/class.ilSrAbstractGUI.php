<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\IRepository;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\UIServices;
use ILIAS\DI\HTTPServices;

/**
 * Class ilSrAbstractMainGUI provides derived GUI classes with common
 * functionalities and dependencies.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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
 * @see ilSrAbstractMainGUI::repeat()
 */
abstract class ilSrAbstractGUI
{
    /**
     * @var string command/method name every derived class must implement.
     */
    public const CMD_INDEX = 'index';

    /**
     * @var string routine-id GET parameter name.
     */
    public const QUERY_PARAM_ROUTINE_ID = 'routine_id';

    /**
     * @var string routine scope (ref-id) GET parameter name.
     */
    public const QUERY_PARAM_ROUTINE_SCOPE  = 'routine_ref_id';

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
     * @var UIServices
     */
    protected $ui;

    /**
     * @var HTTPServices
     */
    protected $http;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @var IRepository
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
        $this->toolbar  = $DIC->toolbar();
        $this->refinery = $DIC->refinery();

        $this->plugin = ilSrLifeCycleManagerPlugin::getInstance();
        $this->repository = new ilSrLifeCycleManagerRepository(
            $DIC->database(),
            $DIC->rbac(),
            $DIC->repositoryTree()
        );
    }

    /**
     * This method SHOULD set up the global template (e.g. page-title etc.).
     *
     * This method is needed due to the plugin implementation as a CronHook
     * plugin. This method is only called if necessary, which means it's
     * called whenever a request DOES NOT have @see ilAdministrationGUI as
     * ilCtrl's base-class.
     *
     * @param ilGlobalTemplateInterface $template
     */
    abstract protected function setupGlobalTemplate(ilGlobalTemplateInterface $template) : void;

    /**
     * This method MUST return an array of valid commands.
     *
     * The returned commands are used as method-names, therefore
     * each string contained in this array MUST be implemented
     * in the derived class.
     *
     * @return string[]
     */
    abstract protected function getCommandList() : array;

    /**
     * This method MUST check if the given user-id can execute the command.
     *
     * The command is passed as an argument in case the permissions
     * differ between the derived classes commands. All access-checks
     * within this method MUST call @see ilSrAccess.
     *
     * @param int    $user_id
     * @param string $command
     * @return bool
     */
    abstract protected function canUserExecuteCommand(int $user_id, string $command) : bool;

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
     * This method dispatches ilCtrl's current command.
     *
     * Derived classes of this GUI are expected to be the last command-
     * class in the control flow, and must therefore dispatch ilCtrl's
     * current command.
     *
     * It may occur that a further command-class is needed, but in this
     * case a redirect should be made.
     */
    public function executeCommand() : void
    {
        // if requests are made from another base-class than the
        // ilAdministrationGUI, the page MUST be set up manually.
        // That's because this plugin is a CronHook plugin and
        // doesn't normally use GUIs.
        if (IRoutine::ORIGIN_TYPE_REPOSITORY === ilSrLifeCycleManagerDispatcher::getOriginTypeFromRequest()) {
            $this->setupGlobalTemplate($this->ui->mainTemplate());
        }

        // get ilCtrl's current command, use index as fallback because
        // this method always exists (due to abstraction).
        $command = $this->ctrl->getCmd(self::CMD_INDEX);

        // get the derived classes available commands and add index
        // even if it's repeatedly.
        $commands = $this->getCommandList();
        $commands[] = self::CMD_INDEX;

        if (in_array($command, $commands, true)) {
            // abort the sync if the derived class never implements the
            // given command/method.
            if (!method_exists(static::class, $command)) {
                throw new LogicException(static::class . " returned command '$command' but the method was never implemented.");
            }

            if ($this->canUserExecuteCommand($this->user->getId(), $command)) {
                $this->beforeCommand($command);
                $this->{$command}();
            } else {
                $this->displayErrorMessage(self::MSG_PERMISSION_DENIED);
            }
        } else {
            $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
        }
    }

    /**
     * This method CAN be overwritten by derived classes, if something
     * needs to happen BEFORE a command is executed.
     *
     * @param string $command
     */
    protected function beforeCommand(string $command) : void { }

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
     * overrides the current back-to tab entry with a custom target.
     *
     * @param string $link
     */
    protected function overrideBack2Target(string $link) : void
    {
        $this->tabs->setBackTarget($this->plugin->txt(self::MSG_BACK_TO), $link);
    }

    /**
     * Always redirects to the command classes index method.
     * @see ilSrAbstractGUI::index()
     */
    protected function repeat() : void
    {
        $this->ctrl->redirectByClass(
            static::class,
            self::CMD_INDEX
        );
    }

    /**
     * Returns the value for a given parameter-name from the requests
     * current GET parameters.
     *
     * If required, the parameter can also be kept alive, by passing true
     * as second argument. This helps to manage requests with the same
     * GET parameter across multiple commands (even after redirects).
     *
     * @param string $parameter_name
     * @param bool   $keep_alive
     * @return string|null
     */
    protected function getQueryParamFromRequest(string $parameter_name, bool $keep_alive = false) : ?string
    {
        $query_params = $this->http->request()->getQueryParams();
        if (isset($query_params[$parameter_name])) {
            if ($keep_alive) {
                $this->ctrl->setParameterByClass(
                    static::class,
                    $parameter_name,
                    $query_params[$parameter_name]
                );
            }

            return $query_params[$parameter_name];
        }

        return null;
    }

    /**
     * Returns a routine fetched from the database for an id provided
     * as a GET parameter.
     *
     * This method is implemented here, as it's used in several derived
     * classes and is somewhat core to the plugin.
     *
     * @param bool $keep_alive
     * @return IRoutine|null
     */
    protected function getRoutineFromRequest(bool $keep_alive = false) : ?IRoutine
    {
        $routine_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_ROUTINE_ID, $keep_alive);
        if (null !== $routine_id) {
            return $this->repository->routine()->get((int) $routine_id);
        }

        return null;
    }

    /**
     * Returns the provided routine scope of the current request.
     *
     * If a scope was provided it is also kept alive, so that
     * further commands can still access it after redirects.
     *
     * @return int|null
     */
    protected function getScopeFromRequest() : ?int
    {
        $scope = $this->getQueryParamFromRequest(self::QUERY_PARAM_ROUTINE_SCOPE, true);
        return ($scope) ? (int) $scope : null;
    }

    /**
     * Helper function to keep a query parameter alive for further link
     * targets and redirects.
     *
     * @param string $parameter_name
     * @return void
     */
    protected function keepAlive(string $parameter_name) : void
    {
        $value = $this->getQueryParamFromRequest($parameter_name);
        if (null !== $value) {
            $this->ctrl->setParameterByClass(
                static::class,
                $parameter_name,
                $value
            );
        }
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
        $this->displayMessageToast($lang_var, 'failure');
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
        $this->displayMessageToast($lang_var, 'success');
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
        $this->displayMessageToast($lang_var, 'info');
    }

    /**
     * displays a message-toast for given lang-var and type on the current page.
     *
     * @param string $lang_var
     * @param string $type (info|success|failure)
     */
    private function displayMessageToast(string $lang_var, string $type) : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render(
                $this->ui->factory()->messageBox()->{$type}(
                    $this->plugin->txt($lang_var)
                )
            )
        );
    }
}