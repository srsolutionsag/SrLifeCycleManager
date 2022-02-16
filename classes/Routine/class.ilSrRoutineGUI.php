<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRoutineGUI
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineGUI extends ilSrAbstractGUI
{
    /**
     * @var string routine scope (ref-id) GET parameter name.
     */
    public const QUERY_PARAM_ROUTINE_SCOPE  = 'routine_ref_id';

    /**
     * ilSrRoutineGUI command names (methods)
     */
    public const CMD_ROUTINE_EDIT   = 'edit';
    public const CMD_ROUTINE_SAVE   = 'save';
    public const CMD_ROUTINE_DELETE = 'delete';

    /**
     * ilSrRoutineGUI action names.
     */
    public const ACTION_ROUTINE_ADD           = 'action_routine_add';
    public const ACTION_ROUTINE_EDIT          = 'action_routine_edit';
    public const ACTION_ROUTINE_DELETE        = 'action_routine_delete';
    public const ACTION_ROUTINE_RULES         = 'action_routine_rules';
    public const ACTION_ROUTINE_NOTIFICATIONS = 'action_routine_notifications';

    /**
     * ilSrRoutineGUI lang vars.
     */
    protected const MSG_ROUTINE_SUCCESS = 'msg_routine_success';
    protected const MSG_ROUTINE_ERROR   = 'msg_routine_error';
    protected const MSG_ORIGIN_UNKNOWN  = 'msg_routine_origin_unknown';
    protected const PAGE_TITLE          = 'page_title_routine';

    /**
     * @var int|null
     */
    protected $origin_type;

    /**
     * @var Routine|null
     */
    protected $routine;

    /**
     * @var int|null
     */
    protected $scope;

    /**
     * ilSrRoutineGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // @TODO: check if this is necessary
        $this->ctrl->saveParameterByClass(
            self::class,
            self::QUERY_PARAM_ROUTINE_SCOPE
        );

        // get dependencies from the current request
        // provided as GET parameters.
        $this->origin_type = $this->getOriginTypeFromRequest();
        $this->routine     = $this->getRoutineFromRequest();
        $this->scope       = $this->getScopeFromRequest();
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template) : void
    {
        $template->setTitle($this->plugin->txt(self::PAGE_TITLE));
    }

    /**
     * @inheritDoc
     */
    protected function getCommandList() : array
    {
        return [
            self::CMD_INDEX,
            self::CMD_ROUTINE_SAVE,
            self::CMD_ROUTINE_EDIT,
            self::CMD_ROUTINE_DELETE,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecuteCommand(int $user_id, string $command) : bool
    {
        // all actions implemented by this GUI require the
        // user to be assigned to at least one configured
        // global role, or the administrator role.
        return ilSrAccess::isUserAssignedToConfiguredRole($user_id) || ilSrAccess::isUserAdministrator($user_id);
    }

    /**
     * @inheritDoc
     */
    protected function beforeCommand(string $command) : void
    {
        // the back-to target is set to the scope the request
        // comes from (which is the case if this GUI is called
        // via tool from the repository).
        if (null !== $this->scope) {
            $this->overrideBack2Target(ilLink::_getLink($this->scope));
        }

        // adds the configuration tabs to the current page
        // before each command is executed.
        $this->addConfigurationTabs(self::TAB_ROUTINE_INDEX);
    }

    /**
     * Displays an action-toolbar and a table to the current page.
     *
     * The table lists all available routines for either the
     * provided scope (ref-id) or the global scope (1).
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->addRoutineToolbar();
        $this->ui->mainTemplate()->setContent(
            $this->getTable()->getHTML()
        );
    }

    /**
     * Displays a routine form on the current page.
     *
     * The form either contains the editable data of a provided
     * routine (id) or an empty form for new creations. When
     * creating a new routine and a scope (ref-id) is provided
     * it will be used by default.
     */
    protected function edit() : void
    {
        // overrides the back-to plugins link with one that
        // redirects back to the routine table (index).
        $this->overrideBack2Target(
            $this->ctrl->getLinkTargetByClass(
                self::class,
                self::CMD_INDEX
            )
        );

        // display the form only if the origin-type could be
        // determined, as it would lead to an error else.
        if (null !== $this->origin_type) {
            $this->getForm()->printToGlobalTemplate();
        } else {
            $this->displayErrorMessage(self::MSG_ORIGIN_UNKNOWN);
        }
    }

    /**
     * Handles the form submission from @see ilSrRoutineGUI::edit().
     *
     * If the submitted data is valid the routine is created or
     * updated in the database and the user gets redirected back
     * to the routines table (index).
     *
     * If the submitted data is invalid or the routine could not
     * be stored, the form with according error messages will be
     * displayed instead.
     */
    protected function save() : void
    {
        $form = $this->getForm();
        if ($form->handleRequest($this->http->request())) {
            // redirect to index if submission was valid.
            $this->sendSuccessMessage(self::MSG_ROUTINE_SUCCESS);
            $this->repeat();
        }

        // display the form if the submission was unsuccessful
        // to display errors.
        $this->displayErrorMessage(self::MSG_ROUTINE_ERROR);
        $this->ui->mainTemplate()->setContent($form->printToGlobalTemplate());
    }

    /**
     * Deletes a provided routine (id) from the database.
     *
     * After trying to delete the routine from the database
     * the user gets redirected back to the routines table
     * (index) with an according error/success message.
     */
    protected function delete() : void
    {
        if (null !== $this->routine) {
            $this->repository->routine()->delete($this->routine);
            $this->sendSuccessMessage(self::MSG_ROUTINE_SUCCESS);
        } else {
            $this->sendErrorMessage(self::MSG_OBJECT_NOT_FOUND);
        }

        $this->repeat();
    }

    /**
     * Returns the origin-type of the current request.
     *
     * The origin-type is determined by ilCtrl's call-history,
     * whereas the base-class is the defining property.
     * Note that this method currently DOES NOT support
     * @see Routine::ORIGIN_TYPE_EXTERNAL.
     *
     * @return int|null
     */
    protected function getOriginTypeFromRequest() : ?int
    {
        // fetch the first array-entry from ilCtrl's call-
        // history. This is always the base-class.
        $call_history = $this->ctrl->getCallHistory();
        $base_class   = array_shift($call_history);

        // check the implementation class-name and return
        // the according origin-type.
        switch ($base_class['class']) {
            case ilUIPluginRouterGUI::class:
                return IRoutine::ORIGIN_TYPE_REPOSITORY;
            case ilAdministrationGUI::class:
                return IRoutine::ORIGIN_TYPE_ADMINISTRATION;

            default:
                return null;
        }
    }

    /**
     * Displays a routine action-toolbar on the current page.
     *
     * The toolbar SHOULD contain actions that cannot be implemented
     * or added to a table-row-entry's dropdown actions (like add
     * for example).
     */
    protected function addRoutineToolbar() : void
    {
        // create a button instance to create new routines.
        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->plugin->txt(self::ACTION_ROUTINE_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_ROUTINE_EDIT
        ));

        $this->toolbar->addButtonInstance($button);
        $this->ui->mainTemplate()->setContent($this->toolbar->getHTML());
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
        $scope = $this->getQueryParamFromRequest(self::QUERY_PARAM_ROUTINE_SCOPE, false);
        return ($scope) ? (int) $scope : null;
    }

    /**
     * Gathers all the routines, if a scope is provided only routines
     * within are considered.
     *
     * @return array
     */
    protected function getTableData() : array
    {
        // if a scope was provided, the table should only
        // display routines within this scope (displayed
        // ref-id's might differ from current scope).
        if (null !== $this->scope) {
            return $this->repository->routine()->getAllByScope($this->scope, true);
        }

        return $this->repository->routine()->getAllAsArray();
    }

    /**
     * Returns the form action for routines. If an existing routine
     * is being edited, the query param will be set first.
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        // if the form has been initialized with a routine,
        // the id must be set as a GET parameter before
        // generating the form-action.
        if (null !== $this->routine) {
            $this->ctrl->setParameterByClass(
                self::class,
                self::QUERY_PARAM_ROUTINE_ID,
                $this->routine->getId()
            );
        }

        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_ROUTINE_SAVE
        );
    }

    /**
     * Helper function that initializes the routine form and
     * returns it.
     *
     * @return ilSrRoutineForm
     */
    protected function getForm() : ilSrRoutineForm
    {
        return new ilSrRoutineForm(
            $this->repository,
            $this->ui->mainTemplate(),
            $this->ui->renderer(),
            $this->form_builders
                ->routine()
                ->withRoutine($this->routine)
                ->withScope($this->scope)
                ->getForm($this->getFormAction())
            ,
            $this->origin_type,
            $this->user->getId()
        );
    }

    /**
     * Helper function that initializes the routine table and
     * returns it.
     *
     * @return ilSrRoutineTable
     */
    protected function getTable() : ilSrRoutineTable
    {
        return new ilSrRoutineTable(
            $this->ui,
            $this->plugin,
            $this,
            self::CMD_INDEX,
            'tpl.routine_table_row.html',
            $this->getTableData()
        );
    }
}