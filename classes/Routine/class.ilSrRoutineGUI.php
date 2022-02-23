<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineForm;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRoutineGUI
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineGUI extends ilSrAbstractGUI
{
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
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var int|null
     */
    protected $scope;

    /**
     * @var RoutineFormBuilder
     */
    protected $form_builder;

    /**
     * ilSrRoutineGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->origin_type = ilSrLifeCycleManagerDispatcher::getOriginTypeFromRequest();
        $this->scope = $this->getScopeFromRequest();
        $this->routine = $this->getRoutineFromRequest() ??
            $this->repository->routine()->getEmpty(
                $this->origin_type,
                $this->user->getId()
            )
        ;

        $this->form_builder = new RoutineFormBuilder(
            $this->ui->factory()->input()->container()->form(),
            $this->ui->factory()->input()->field(),
            $this->refinery,
            $this->plugin,
            $this->getFormAction(),
            $this->routine
        );
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
        // the index command can always be executed because
        // routines must be visible to object tutors etc.
        if (self::CMD_INDEX === $command) {
            return true;
        }

        // administrators should be able to execute all commands.
        if (ilSrAccess::isUserAdministrator($user_id)) {
            return true;
        }

        // if the current routine is already stored, check if the
        // user is the owner and can therefore execute all commands.
        if (null !== $this->routine->getRoutineId() && ilSrAccess::isUserAssignedToConfiguredRole($user_id)) {
            return $user_id === $this->routine->getOwnerId();
        }

        return false;
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
        // only display the toolbar if the user can manage them.
        if (ilSrAccess::isUserAssignedToConfiguredRole($this->user->getId())) {
            $this->addRoutineToolbar();
        }

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
            $this->ui->mainTemplate()->setContent(
                $this->getForm()->render()
            );
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
        $this->ui->mainTemplate()->setContent(
            $form->render()
        );
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

        return $this->repository->routine()->getAll(true);
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
                $this->routine->getRoutineId()
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
     * @return RoutineForm
     */
    protected function getForm() : RoutineForm
    {
        return new RoutineForm(
            $this->repository,
            $this->ui->renderer(),
            $this->form_builder
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
            $this->getTableData(),
            $this->user
        );
    }
}