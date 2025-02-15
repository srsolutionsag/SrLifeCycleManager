<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleFormDirector;
use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleFormBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Form\Rule\RuleFormProcessor;

/**
 * This GUI class is responsible for all actions regarding rules.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRuleGUI extends ilSrAbstractGUI
{
    // ilSrRuleGUI GET-parameter names:
    public const PARAM_RULE_ID = 'rule_id';

    // ilSrRuleGUI command/method names:
    public const CMD_RULE_EDIT = 'edit';
    public const CMD_RULE_SAVE = 'save';
    public const CMD_RULE_DELETE = 'delete';

    // ilSrRuleGUI language variables:
    protected const MSG_ROUTINE_NOT_FOUND = 'msg_routine_not_found';
    protected const MSG_RULE_SUCCESS = 'msg_rule_success';
    protected const MSG_RULE_ERROR = 'msg_rule_error';
    protected const PAGE_TITLE = 'page_title_rules';

    protected AttributeFactory $attribute_factory;

    protected RuleFormDirector $form_director;

    protected IRule $rule;

    /**
     * Initializes the form-builder director and fetches required
     * request parameters.
     *
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->panicOnMissingRoutine();

        $this->rule =
            $this->getRequestedRule() ??
            $this->repository->rule()->empty($this->routine);

        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilSrLifeCycleManagerPlugin */
        $plugin = $component_factory->getPlugin(ilSrLifeCycleManagerPlugin::PLUGIN_ID);

        $this->attribute_factory = $plugin->getContainer()->getAttributeFactory();

        $this->form_director = new RuleFormDirector(
            new RuleFormBuilder(
                $this->translator,
                $this->ui_factory->input()->container()->form(),
                $this->ui_factory->input()->field(),
                $this->refinery,
                $this->attribute_factory,
                $this->rule,
                $this->getFormAction(
                    self::CMD_RULE_SAVE,
                    self::PARAM_RULE_ID
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab()
            ->addRoutineTab()
            ->addPreviewTab()
            ->deactivateTabs()
            ->addBackToIndex(self::class);
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        // only routine-managers can execute commands in this gui.
        return $this->access_handler->canManageRoutines();
    }

    /**
     * Displays all existing rules that are related to the requested routine.
     */
    protected function index(): void
    {
        $table = new ilSrRuleTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->repository->general(),
            $this->attribute_factory,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->rule()->getByRoutine($this->routine, true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->toolbar_manager->addRuleToolbar();
        $this->render($table->getTable());
    }

    /**
     * Displays the rule form on the current page.
     *
     * If a rule is requested, the form-director already was initialized
     * with the according data, therefore this method can be used for
     * create AND update commands.
     */
    protected function edit(): void
    {
        $this->render($this->form_director->getFormByRoutine($this->routine));
    }

    /**
     * Processes the submitted rule-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrRuleGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    protected function save(): void
    {
        $processor = new RuleFormProcessor(
            $this->repository->rule(),
            $this->request,
            $this->form_director->getFormByRoutine($this->routine),
            $this->rule
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_RULE_SUCCESS);
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * Deletes the requested routine and redirects the user back to
     * @see ilSrRoutineGUI::index().
     */
    protected function delete(): void
    {
        if (null !== $this->rule->getRuleId()) {
            $this->sendSuccessMessage(self::MSG_RULE_SUCCESS);
            $this->repository->rule()->delete($this->rule);
        } else {
            $this->sendErrorMessage(self::MSG_RULE_ERROR);
        }

        $this->cancel();
    }

    /**
     * Fetches the requested rule from the database if an id was provided.
     *
     * @return IRule|null
     */
    protected function getRequestedRule(): ?IRule
    {
        $rule_id = $this->getRequestParameter(self::PARAM_RULE_ID);
        if (null !== $rule_id) {
            return $this->repository->rule()->get((int) $rule_id);
        }

        return null;
    }
}
