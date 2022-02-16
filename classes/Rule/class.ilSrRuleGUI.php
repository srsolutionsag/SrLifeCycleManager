<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Class ilSrRuleGUI
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRuleGUI extends ilSrAbstractGUI
{
    public const QUERY_PARAM_RULE_ID = 'rule_id';

    public const ACTION_RULE_ADD    = 'action_rule_add';
    public const ACTION_RULE_DELETE = 'action_rule_delete';

    public const CMD_RULE_ADD       = 'add';
    public const CMD_RULE_SAVE      = 'save';
    public const CMD_RULE_DELETE    = 'delete';

    protected const MSG_ROUTINE_NOT_FOUND = 'msg_routine_not_found';
    protected const MSG_RULE_SUCCESS      = 'msg_rule_success';
    protected const MSG_RULE_ERROR        = 'msg_rule_error';
    protected const PAGE_TITLE            = 'page_title_rules';

    /**
     * @var IRoutine|null
     */
    protected $routine;

    /**
     * ilSrRuleGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // get dependencies from the current request
        // provided as GET parameters.
        $this->routine = $this->getRoutineFromRequest(true);
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
            self::CMD_RULE_ADD,
            self::CMD_RULE_SAVE,
            self::CMD_RULE_DELETE
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
        // abort if no routine was provided, as all actions
        // of this GUI depend on it.
        if (null === $this->routine) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }

        // add the configuration tabs to the current page
        // and deactivate all tabs by passing an invalid
        // character as active tab-id.
        $this->addConfigurationTabs('§');
    }

    /**
     * Displays a rule table on the current page.
     *
     * The table lists all existing rules related to the
     * routine-id provided as a GET parameter.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        // override the back-to tab with one that redirects back
        // to the routine GUI.
        $this->overrideBack2Target(
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                self::CMD_INDEX
            )
        );

        $this->addRuleToolbar();
        $this->ui->mainTemplate()->setContent(
            $this->getTable()->getHTML()
        );
    }

    /**
     * Displays a form to create new rules.
     *
     * The method does however not process it, the form will be
     * submitted to @see ilSrRuleGUI::save().
     */
    protected function add() : void
    {
        // override the back-to tab with one that redirects
        // back to index.
        $this->overrideBack2Target(
            $this->ctrl->getLinkTargetByClass(
                self::class,
                self::CMD_INDEX
            )
        );

        $this->getForm()->printToGlobalTemplate();
    }

    /**
     * Processes the submitted form-data and creates a new rule that is
     * related to the current routine.
     *
     * If the creation fails or any inputs are invalid, the form will
     * be displayed again with an according error message.
     */
    protected function save() : void
    {
        $form = $this->getForm();
        if ($form->handleRequest($this->http->request())) {
            // redirect to index if submission was valid.
            $this->sendSuccessMessage(self::MSG_RULE_SUCCESS);
            $this->repeat();
        }

        // display the form if the submission was unsuccessful
        // to display errors.
        $this->displayErrorMessage(self::MSG_RULE_ERROR);
        $form->printToGlobalTemplate();
    }

    /**
     * Deletes the provided rule and removes the relation
     * between it and the provided routine.
     */
    protected function delete() : void
    {
        $rule = $this->getRuleFromRequest();
        if (null !== $rule) {
            $this->repository->rule()->delete($rule);
            $this->sendSuccessMessage(self::MSG_RULE_SUCCESS);
        } else {
            $this->sendErrorMessage(self::MSG_RULE_ERROR);
        }

        $this->repeat();
    }

    /**
     * Displays a rule action-toolbar on the current page.
     *
     * The toolbar SHOULD contain actions that cannot be implemented
     * or added to a table-row-entry's dropdown actions (like add
     * for example).
     */
    protected function addRuleToolbar() : void
    {
        // create a button instance to create new routines.
        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->plugin->txt(self::ACTION_RULE_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_RULE_ADD
        ));

        $this->toolbar->addButtonInstance($button);
        $this->ui->mainTemplate()->setContent($this->toolbar->getHTML());
    }

    /**
     * Returns the provided rule of the current request.
     *
     * @return Rule|null
     */
    protected function getRuleFromRequest() : ?Rule
    {
        $rule_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_RULE_ID);
        if (null !== $rule_id) {
            return $this->repository->rule()->get((int) $rule_id);
        }

        return null;
    }

    /**
     * Returns the form action for rules.
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        // the current routine must be passed along so the
        // relationship can be created.
        $this->ctrl->setParameterByClass(
            self::class,
            self::QUERY_PARAM_ROUTINE_ID,
            $this->routine->getId()
        );

        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_RULE_SAVE
        );
    }

    /**
     * Gathers all the rules for related to the current routine.
     *
     * @return array
     */
    protected function getTableData() : array
    {
        return $this->repository->routine()->getRules($this->routine, true);
    }

    /**
     * Helper function that initializes the rule-form
     * and returns it.
     *
     * @return ilSrRuleForm
     */
    protected function getForm() : ilSrRuleForm
    {
        return new ilSrRuleForm(
            $this->repository,
            $this->ui->mainTemplate(),
            $this->ui->renderer(),
            $this->form_builders
                ->rule()
                ->getForm($this->getFormAction())
            ,
            $this->routine
        );
    }

    /**
     * Helper function that initializes the rule-table
     * and returns it.
     *
     * @return ilSrRuleTable
     */
    protected function getTable() : ilSrRuleTable
    {
        return new ilSrRuleTable(
            $this->ui,
            $this->plugin,
            $this,
            self::CMD_INDEX,
            'tpl.rule_table_row.html',
            $this->getTableData(),
            $this->routine
        );
    }
}