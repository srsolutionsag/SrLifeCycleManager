<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
 * This is an abstraction for ILIAS command-class implementations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The gui-class wraps common dependencies, so that derived classes can
 * slim down their constructor.
 *
 * To enforce the usage of UI components, when rendering content in a
 * derived class, only the method @see ilSrAbstractGUI::render() should
 * be used.
 *
 * A notable structural point is, that all derived classes must also implement
 * an index method @see ilSrAbstractGUI::index().
 * The benefit of having an index method is, that redirects to a GUI class
 * can always be made the same, pointing to @see ilSrAbstractGUI::CMD_INDEX.
 *
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractGUI
{
    /**
     * @var string the method name each derived class must implement.
     */
    public const CMD_INDEX = 'index';

    /**
     * GET-parameter names.
     */
    public const PARAM_ROUTINE_ID = 'routine_id';
    public const PARAM_OBJECT_REF_ID = 'ref_id';

    /**
     * common language variables.
     */
    protected const MSG_PERMISSION_DENIED = 'msg_permission_denied';
    protected const MSG_ROUTINE_NOT_FOUND = 'msg_routine_not_found';
    protected const MSG_OBJECT_NOT_FOUND = 'msg_object_not_found';

    // Plugin dependencies:

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var RepositoryFactory
     */
    protected $repository;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilSrToolbarManager
     */
    protected $toolbar_manager;

    /**
     * @var ilSrTabManager
     */
    protected $tab_manager;

    /**
     * @var int|null
     */
    protected $object_ref_id;

    /**
     * @var int
     */
    protected $origin;

    // ILIAS dependencies:

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * This instance variable is private to enforce the usage of UI components
     * through @see ilSrAbstractGUI::render().
     *
     * @var ilGlobalTemplateInterface
     */
    private $global_template;

    /**
     * Initializes common dependencies which are used in every derived GUI class.
     *
     * @throws LogicException if required dependencies are missing.
     */
    public function __construct()
    {
        global $DIC;

        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilSrLifeCycleManagerPlugin */
        $plugin = $component_factory->getPlugin(ilSrLifeCycleManagerPlugin::PLUGIN_ID);

        $container = $plugin->getContainer();

        $this->origin = ilSrLifeCycleManagerDispatcherGUI::getOriginType();
        $this->translator = $container->getTranslator();
        $this->global_template = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->database = $DIC->database();

        $this->repository = $container->getRepositoryFactory();
        $this->access_handler = $container->getAccessHandler();

        $this->toolbar_manager = new ilSrToolbarManager(
            $this->access_handler,
            $this->global_template,
            $this->translator,
            $DIC->toolbar(),
            $this->ctrl
        );

        $this->tab_manager = new ilSrTabManager(
            $this->access_handler,
            $this->translator,
            $DIC->tabs(),
            $this->ctrl,
            $this->origin
        );

        $this->object_ref_id = $this->getRequestedObject();
        $this->routine = $this->getRequestedRoutine();

        $this->keepNecessaryParametersAlive();
    }

    /**
     * This method dispatches ilCtrl's current command.
     *
     * Derived classes of this GUI are expected to be the last command-
     * class in the control flow, and must therefore dispatch ilCtrl's
     * command.
     */
    public function executeCommand(): void
    {
        $command = $this->ctrl->getCmd(self::CMD_INDEX);
        if (!method_exists(static::class, $command)) {
            throw new LogicException(static::class . " does not implement method '$command'.");
        }

        $this->setupGlobalTemplate(
            $this->global_template,
            $this->tab_manager
        );

        if (!$this->canUserExecute($this->access_handler, $command)) {
            $this->displayErrorMessage(self::MSG_PERMISSION_DENIED);
        } else {
            $this->{$command}();
        }
    }

    /**
     * This method MUST set up the current page (global template).
     * It should manage things like the page-title, -description or tabs.
     *
     * @param ilGlobalTemplateInterface $template
     * @param ilSrTabManager            $tabs
     */
    abstract protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void;

    /**
     * This method MUST check if the given user can execute the command.
     * Note that all actions should be accessible for administrators.
     *
     * The command is passed as an argument in case the permissions
     * differ between the derived classes commands.
     *
     * @param ilSrAccessHandler $access_handler
     * @param string            $command
     * @return bool
     */
    abstract protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool;

    /**
     * This method is the entry point of the command class.
     *
     * Redirects are (almost) always made to this method, when
     * coming from another GUI class.
     *
     * @see ilSrAbstractGUI::cancel() can also be used within
     * the same GUI class.
     */
    abstract protected function index(): void;

    /**
     * Redirects back to the derived classes index method.
     *
     * @see ilSrAbstractGUI::index()
     */
    protected function cancel(): void
    {
        $this->ctrl->redirectByClass(
            static::class,
            self::CMD_INDEX
        );
    }

    /**
     * Fetches the requested routine from the database, if a routine id was provided.
     * Otherwise, an empty instance will be returned.
     *
     * @return IRoutine
     */
    protected function getRequestedRoutine(): IRoutine
    {
        $routine_id = $this->getRequestParameter(self::PARAM_ROUTINE_ID);
        $routine = null;

        if (!empty($routine_id)) {
            $routine = $this->repository->routine()->get((int) $routine_id);
        }

        return $routine ?? $this->repository->routine()->empty($this->user->getId(), $this->origin);
    }

    /**
     * Returns the requested object ref-id from the request, if the request
     * was made within the repository context.
     *
     * @return int|null
     */
    protected function getRequestedObject(): ?int
    {
        // only consider request objects from the repository. for example
        // the configuration context also provides a ref-id, which doesn't
        // belong to a repository object.
        if (IRoutine::ORIGIN_TYPE_REPOSITORY !== $this->origin) {
            return null;
        }

        $requested_object = $this->getRequestParameter(self::PARAM_OBJECT_REF_ID);
        if (!empty($requested_object)) {
            return (int) $requested_object;
        }

        return null;
    }

    /**
     * Returns the value for a given parameter-name from the requests
     * current GET parameters.
     *
     * @param string $parameter
     * @return string|null
     */
    protected function getRequestParameter(string $parameter): ?string
    {
        return $this->request->getQueryParams()[$parameter] ?? null;
    }

    /**
     * Renders a given UI component to the current page (global template).
     *
     * @param Component $component
     */
    protected function render(Component $component): void
    {
        $this->global_template->setContent(
            $this->renderer->render($component)
        );
    }

    /**
     * Helper function that aborts (throws an exception) if the requested
     * routine is not stored yet, but required.
     *
     * @throws LogicException
     */
    protected function panicOnMissingRoutine(): void
    {
        if (null === $this->routine->getRoutineId()) {
            throw new LogicException($this->translator->txt(self::MSG_ROUTINE_NOT_FOUND));
        }
    }

    /**
     * Helper function that redirects to the given object (ref-id).
     *
     * @param int $ref_id
     */
    protected function redirectToRefId(int $ref_id): void
    {
        $this->ctrl->redirectToURL(ilLink::_getLink($ref_id));
    }

    /**
     * Returns a form-action for the given command of the derived class.
     *
     * If a query-parameter is provided, the method checks if a value has been
     * submitted ($_GET) and if so, the parameter will be appended or used for
     * the form-action.
     *
     * @param string      $command
     * @param string|null $query_parameter
     * @return string
     */
    protected function getFormAction(string $command, string $query_parameter = null): string
    {
        // temporarily safe the parameter value if it has been requested.
        if (null !== $query_parameter &&
            null !== ($query_value = $this->getRequestParameter($query_parameter))
        ) {
            $this->ctrl->setParameterByClass(static::class, $query_parameter, $query_value);
        }

        // build the form action while the query value (maybe) is set.
        $form_action = $this->ctrl->getFormActionByClass(
            static::class,
            $command
        );

        // remove the parameter again once the form action has been generated.
        if (null !== $query_parameter) {
            $this->ctrl->clearParameterByClass(static::class, $query_parameter);
        }

        return $form_action;
    }

    /**
     * displays an error message for given lang-var on the next page (redirect).
     */
    protected function sendErrorMessage(string $lang_var, bool $translate = true): void
    {
        $this->global_template->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
            ($translate) ? $this->translator->txt($lang_var) : $lang_var,
            true
        );
    }

    /**
     * displays an error message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displayErrorMessage(string $lang_var): void
    {
        $this->displayMessageToast($lang_var, 'failure');
    }

    /**
     * displays a success message for given lang-var on the next page (redirect).
     */
    protected function sendSuccessMessage(string $lang_var, bool $translate = true): void
    {
        $this->global_template->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            ($translate) ? $this->translator->txt($lang_var) : $lang_var,
            true
        );
    }

    /**
     * displays an success message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displaySuccessMessage(string $lang_var): void
    {
        $this->displayMessageToast($lang_var, 'success');
    }

    /**
     * displays an info message for given lang-var on the next page (redirect).
     */
    protected function sendInfoMessage(string $lang_var, bool $translate = true): void
    {
        $this->global_template->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
            ($translate) ? $this->translator->txt($lang_var) : $lang_var,
            true
        );
    }

    /**
     * displays an info message for given lang-var on the current page.
     *
     * @param string $lang_var
     */
    protected function displayInfoMessage(string $lang_var): void
    {
        $this->displayMessageToast($lang_var, 'info');
    }

    /**
     * This method keeps all query-parameters alive that are required
     * throughout the derived classes.
     *
     * This method exists thanks to the super annoying way ilCtrl handles
     * query-parameters when generating link targets. For laziness's sake,
     * this function will append the most used parameters to each possible
     * target class, so you don't have to do that every time :).
     */
    private function keepNecessaryParametersAlive(): void
    {
        // the request object must be saved individually for each derived class
        // and cannot be saved via 'static::class', because then ilCtrl would only
        // consider the parameter for the instantiated object.
        // this has to be done in order to generate links to different GUI classes
        // and keeping alive the ref_id parameter, once provided.
        $this->ctrl->saveParameterByClass(ilSrRoutineGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrRoutineAssignmentGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrObjectAssignmentGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrRuleGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrConfirmationGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrWhitelistGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrRoutinePreviewGUI::class, self::PARAM_OBJECT_REF_ID);

        // save the ref-id for the configuration GUI as well if the context is NOT
        // administration (otherwise ref-id would be the plugin).
        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION !== $this->origin) {
            $this->ctrl->saveParameterByClass(ilSrConfigGUI::class, self::PARAM_OBJECT_REF_ID);
        }

        // save the routine-id parameter for all derived classes except routine-assignment-
        // and routine-gui, otherwise the link-generation might misbehave.
        if (ilSrRoutineAssignmentGUI::class !== static::class &&
            ilSrRoutineGUI::class !== static::class
        ) {
            $this->ctrl->saveParameterByClass(static::class, self::PARAM_ROUTINE_ID);
        }
    }

    /**
     * displays a message-toast for given lang-var and type on the current page.
     *
     * @param string $lang_var
     * @param string $type (info|success|failure)
     */
    private function displayMessageToast(string $lang_var, string $type): void
    {
        $this->render(
            $this->ui_factory->messageBox()->{$type}(
                $this->translator->txt($lang_var)
            )
        );
    }
}
