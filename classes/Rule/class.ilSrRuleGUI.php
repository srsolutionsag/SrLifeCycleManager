<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineAwareRule;
use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleForm;
use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleFormDirector;

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
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var IRoutineAwareRule|null
     */
    protected $rule;

    /**
     * @var int|null
     */
    protected $scope;

    /**
     * @var RuleFormBuilder
     */
    protected $form_builder;

    /**
     * ilSrRuleGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->routine = $this->getRoutineFromRequest(true);
        $this->rule = $this->getRuleFromRequest();
        $this->scope = $this->getScopeFromRequest();

        $this->form_builder = new RuleFormBuilder(
            $this->ui->factory()->input()->container()->form(),
            $this->ui->factory()->input()->field(),
            $this->refinery,
            $this->plugin,
            $this->getFormAction()
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
        // the index command can always be executed because
        // rules must be visible to object tutors etc.
        if (self::CMD_INDEX === $command) {
            return true;
        }

        // administrators should be able to execute all commands.
        if (ilSrAccess::isUserAdministrator($user_id)) {
            return true;
        }

        // if the current user is the owner of the related routine,
        // he should be able to execute all commands too.
        if (null !== $this->routine && ilSrAccess::isUserAssignedToConfiguredRole($user_id)) {
            return ($user_id === $this->routine->getOwnerId());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function beforeCommand(string $command) : void
    {
        // add the configuration tabs to the current page
        // and deactivate all tabs by passing an invalid
        // character as active tab-id.
        $this->addConfigurationTabs('§');

        // abort if no routine was provided, as all actions
        // of this GUI depend on it.
        if (null === $this->routine) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }
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

        // only display the toolbar for routine owners or administrators.
        if ($this->routine->getOwnerId() === $this->user->getId() ||
            ilSrAccess::isUserAdministrator($this->user->getId())
        ) {
            $this->addRuleToolbar();
        }

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

        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
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
        $this->ui->mainTemplate()->setContent(
            $form->render()
        );
    }

    /**
     * Deletes the provided rule and removes the relation
     * between it and the provided routine.
     */
    protected function delete() : void
    {
        $rule = $this->getRuleFromRequest();
        if (null !== $this->routine && null !== $rule) {
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
     * @return IRoutineAwareRule|null
     */
    protected function getRuleFromRequest() : ?IRoutineAwareRule
    {
        $rule_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_RULE_ID);
        if (null !== $rule_id) {
            return
                $this->repository->rule()->get($this->routine->getRoutineId(), (int) $rule_id) ??
                $this->repository->rule()->getEmpty($this->routine->getRoutineId())
            ;
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
            $this->routine->getRoutineId()
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
        return $this->repository->rule()->getAll($this->routine->getRoutineId(), true);
    }

    /**
     * Helper function that initializes the rule-form
     * and returns it.
     *
     * @return RuleForm
     */
    protected function getForm() : RuleForm
    {
        $director = new RuleFormDirector(
            $this->ui->renderer(),
            $this->form_builder,
            $this->repository,
            $this->rule ?? $this->repository->rule()->getEmpty(
                $this->routine->getRoutineId()
            )
        );

        return $director->getCourseAttributeForm();
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
            $this->routine,
            $this->user
        );
    }
}