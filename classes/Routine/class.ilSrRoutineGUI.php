<?php

use srag\Plugins\SrLifeCycleManager\Routine\Routine;

/**
 * Class ilSrRoutineGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineGUI extends ilSrAbstractMainGUI
{
    /**
     * @var string routine-id GET parameter name.
     */
    public const QUERY_PARAM_ROUTINE_ID  = 'routine_id';

    /**
     * @var string origin-type GET parameter name.
     */
    public const QUERY_PARAM_ORIGIN_TYPE = 'origin_type';

    /**
     * ilSrRoutineGUI command names (methods)
     */
    public const CMD_ROUTINE_EDIT        = 'edit';
    public const CMD_ROUTINE_SAVE        = 'save';
    public const CMD_ROUTINE_DELETE      = 'delete';
    public const CMD_ROUTINE_RULE_MANAGE = 'manageRules';
    public const CMD_ROUTINE_RULE_ADD    = 'addRule';
    public const CMD_ROUTINE_RULE_REMOVE = 'removeRule';

    /**
     * ilSrRoutineGUI action names.
     */
    public const ACTION_ROUTINE_ADD    = 'action_routine_add';
    public const ACTION_ROUTINE_EDIT   = 'action_routine_edit';
    public const ACTION_ROUTINE_DELETE = 'action_routine_delete';
    public const ACTION_ROUTINE_RULES  = 'action_routine_rules';

    /**
     * ilSrRoutineGUI lang vars.
     */
    private const MSG_ROUTINE_SUCCESS = 'msg_routine_success';
    private const MSG_ROUTINE_ERROR   = 'msg_routine_error';
    private const PAGE_TITLE          = 'page_title_routine';

    /**
     * @var int
     */
    private $origin_type;

    /**
     * ilSrRoutineGUI constructor.
     *
     * @param int $origin_type
     */
    public function __construct(int $origin_type)
    {
        $this->origin_type = $origin_type;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);
        switch ($cmd) {
            case self::CMD_INDEX:
            case self::CMD_ROUTINE_SAVE:
            case self::CMD_ROUTINE_EDIT:
            case self::CMD_ROUTINE_DELETE:
            case self::CMD_ROUTINE_RULE_MANAGE:
            case self::CMD_ROUTINE_RULE_ADD:
            case self::CMD_ROUTINE_RULE_REMOVE:
                if (ilSrAccess::canUserManageRoutines($this->user->getId())) {
                    // add configuration tabs and execute given command.
                    $this->addConfigurationTabs(self::TAB_ROUTINE_INDEX);
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

    /**
     * @inheritDoc
     */
    protected function getPageTitle() : string
    {
        return $this->plugin->txt(self::PAGE_TITLE);
    }

    /**
     * @inheritDoc
     */
    protected function getPageDescription() : string
    {
        return '';
    }

    /**
     * Adds an actions-toolbar and a table that lists all stored
     * routines to the current page.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->addRoutineToolbar();
        $table = new ilSrRoutineTable($this, self::CMD_INDEX);
        $this->ui->mainTemplate()->setContent($table->getHTML());
    }

    /**
     * displays a form for the routine provided in the query-params
     * or an empty one (for new creations).
     */
    private function edit() : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
    }

    /**
     * stores the routine in the database and redirects back to index or
     * edit, if the process was not successful.
     */
    private function save() : void
    {
        $form = $this->getForm();
        if ($form->handleRequest($this->http->request())) {
            // redirect to index if submission was valid.
            $this->sendSuccessMessage(self::MSG_ROUTINE_SUCCESS);
            $this->cancel();
        }

        // display the form if the submission was unsuccessful.
        $this->displayErrorMessage(self::MSG_ROUTINE_ERROR);
        $this->ui->mainTemplate()->setContent($form->render());
    }

    /**
     * removes a routine and associated entities from the database
     * and redirects back to index.
     */
    private function delete() : void
    {
        $this->cancel();
    }

    /**
     * displays a table which lists all created routine-rule
     * associations.
     */
    private function manageRules() : void
    {

    }

    /**
     * displays/starts the rule creation process and displays
     * forms provided by this component.
     */
    private function addRule() : void
    {

    }

    /**
     * removes a routine-rule association from the database and
     * redirects back to manageRules.
     */
    private function removeRule() : void
    {

    }

    /**
     * Adds a toolbar with actions to the current page.
     */
    private function addRoutineToolbar() : void
    {
        // create a button instance to create new routines.
        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->plugin->txt(self::ACTION_ROUTINE_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_ROUTINE_EDIT
        ));

        // add button instance to toolbar.
        $this->toolbar->addButtonInstance($button);

        // add toolbar to the current page.
        $this->ui->mainTemplate()->setContent($this->toolbar->getHTML());
    }

    /**
     * Fetches a routine from the database for the id provided as a
     * query-param ( @see ilSrRoutineGUI::QUERY_PARAM_ROUTINE_ID ).
     *
     * @return Routine|null
     */
    private function getRoutineFromRequest() : ?Routine
    {
        $query_params = $this->http->request()->getQueryParams();
        if (isset($query_params[self::QUERY_PARAM_ROUTINE_ID])) {
            return $this->repository->routine()->get($query_params[self::QUERY_PARAM_ROUTINE_ID]);
        }

        return null;
    }

    /**
     * Helper function that initialises the routine form and
     * returns it.
     *
     * @return ilSrRoutineForm
     */
    private function getForm() : ilSrRoutineForm
    {
        return new ilSrRoutineForm(
            $this->origin_type,
            $this->user->getId(),
            $this->getRoutineFromRequest()
        );
    }
}