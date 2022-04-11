<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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

        $this->origin = ilSrLifeCycleManagerDispatcher::getOriginType();
        $this->translator = ilSrLifeCycleManagerPlugin::getInstance();
        $this->global_template = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->repository = new RepositoryFactory(
            new ilSrGeneralRepository($DIC->database(), $DIC->repositoryTree(), $DIC->rbac()),
            new ilSrConfigRepository($DIC->database(), $DIC->rbac()),
            new ilSrRoutineRepository($DIC->database(), $DIC->repositoryTree()),
            new ilSrRoutineAssignmentRepository($DIC->database()),
            new ilSrRuleRepository($DIC->database(), $DIC->repositoryTree()),
            new ilSrNotificationRepository($DIC->database()),
            new ilSrWhitelistRepository($DIC->database())
        );

        $this->access_handler = new ilSrAccessHandler(
            $DIC->rbac(),
            $this->repository->config()->get(),
            $DIC->user()
        );

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
            $this->ctrl
        );

        $this->object_ref_id = $this->getRequestedObject();
        $this->routine = $this->getRequestedRoutine();

        // save current request object for all implementing classes.
        // this cannot be done via static::class, because when building
        // link targets to another gui the parameter must be considered.
        $this->ctrl->saveParameterByClass(ilSrRoutineGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrRoutineAssignmentGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrRuleGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrNotificationGUI::class, self::PARAM_OBJECT_REF_ID);
        $this->ctrl->saveParameterByClass(ilSrWhitelistGUI::class, self::PARAM_OBJECT_REF_ID);

        // save current routine-id if provided for all derived classes.
        $this->ctrl->saveParameterByClass(static::class, self::PARAM_ROUTINE_ID);
    }

    /**
     * This method dispatches ilCtrl's current command.
     *
     * Derived classes of this GUI are expected to be the last command-
     * class in the control flow, and must therefore dispatch ilCtrl's
     * command.
     */
    public function executeCommand() : void
    {
        $command = $this->ctrl->getCmd(self::CMD_INDEX);
        if (!method_exists(static::class, $command)) {
            throw new LogicException(static::class ." does not implement method '$command'.");
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
    abstract protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs) : void;

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
    abstract protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool;

    /**
     * This method is the entry point of the command class.
     *
     * Redirects are (almost) always made to this method, when
     * coming from another GUI class.
     *
     * @see ilSrAbstractGUI::cancel() can also be used within
     * the same GUI class.
     */
    abstract protected function index() : void;

    /**
     * Redirects back to the derived classes index method.
     *
     * @see ilSrAbstractGUI::index()
     */
    protected function cancel() : void
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
    protected function getRequestedRoutine() : IRoutine
    {
        $routine_id = $this->getRequestParameter(self::PARAM_ROUTINE_ID);
        $routine = null;

        if (null !== $routine_id) {
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
    protected function getRequestedObject() : ?int
    {
        // only consider request objects from the repository. for example
        // the configuration context also provides a ref-id, which doesn't
        // belong to a repository object.
        if (IRoutine::ORIGIN_TYPE_REPOSITORY !== $this->origin) {
            return null;
        }

        $requested_object = $this->getRequestParameter(self::PARAM_OBJECT_REF_ID);
        if (null !== $requested_object) {
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
    protected function getRequestParameter(string $parameter) : ?string
    {
        return $this->request->getQueryParams()[$parameter] ?? null;
    }

    /**
     * Renders a given UI component to the current page (global template).
     *
     * @param Component $component
     */
    protected function render(Component $component) : void
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
    protected function panicOnMissingRoutine() : void
    {
        if (null === $this->routine->getRoutineId()) {
            throw new LogicException($this->translator->txt(self::MSG_ROUTINE_NOT_FOUND));
        }
    }

    /**
     * displays an error message for given lang-var on the next page (redirect).
     *
     * @param string $lang_var
     */
    protected function sendErrorMessage(string $lang_var) : void
    {
        ilUtil::sendFailure($this->translator->txt($lang_var), true);
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
        ilUtil::sendSuccess($this->translator->txt($lang_var), true);
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
        ilUtil::sendInfo($this->translator->txt($lang_var), true);
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
        $this->render(
            $this->ui_factory->messageBox()->{$type}(
                $this->translator->txt($lang_var)
            )
        );
    }
}