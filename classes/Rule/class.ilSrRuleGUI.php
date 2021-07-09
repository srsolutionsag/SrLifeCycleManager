<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Class ilSrRuleGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRuleGUI extends ilSrAbstractMainGUI
{
    /**
     * @var string rule id GET parameter name.
     */
    public const QUERY_PARAM_RULE_ID = 'rule_id';

    /**
     * ilSrRuleGUI action names.
     */
    public const ACTION_RULE_ADD    = 'action_rule_add';
    public const ACTION_RULE_DELETE = 'action_rule_delete';

    /**
     * ilSrRuleGUI command names.
     */
    public const CMD_RULE_ADD = 'add';

    /**
     * ilSrRuleGUI message lang-vars.
     */
    private const MSG_ROUTINE_NOT_FOUND = 'msg_routine_not_found';
    private const MSG_RULE_SUCCESS      = 'msg_rule_success';
    private const MSG_RULE_ERROR        = 'msg_rule_error';
    private const PAGE_TITLE            = 'page_title_rules';

    /**
     * @var IRoutine|null
     */
    private $routine;

    /**
     * ilSrRuleGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // get dependencies from the current request
        // provided as GET parameters.
        $this->routine = $this->getRoutineFromRequest();
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
        // an deactivate all tabs by passing an 'invalid'
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
     *
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
    private function addRuleToolbar() : void
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
    private function getRuleFromRequest() : ?Rule
    {
        $rule_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_RULE_ID);
        if (null !== $rule_id) {
            return $this->repository->rule()->get($rule_id);
        }

        return null;
    }

    /**
     * Helper function that initializes the rule-table
     * and returns it.
     *
     * @return ilSrRuleTable
     */
    private function getTable() : ilSrRuleTable
    {
        return new ilSrRuleTable(
            $this,
            self::CMD_INDEX,
            $this->routine
        );
    }
}