<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\ITranslator;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This class is responsible for managing the plugin toolbars.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This helper class is meant to centralize the toolbar implementation
 * and simplify their management. Methods that add or render toolbars
 * must never do so fluently, because there should only be exactly ONE
 * toolbar on every page.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrToolbarManager
{
    // ilSrToolbarManager toolbar actions:
    public const ACTION_ASSIGNMENT_ADD = 'action_routine_assignment_add';
    public const ACTION_ROUTINE_ADD = 'action_routine_add';
    public const ACTION_CONFIRMATION_ADD = 'action_confirmation_add';
    public const ACTION_REMINDER_ADD = 'action_reminder_add';
    public const ACTION_RULE_ADD = 'action_rule_add';

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $global_template;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilSrAccessHandler         $access_handler
     * @param ilGlobalTemplateInterface $global_template
     * @param ITranslator               $translator
     * @param ilToolbarGUI              $toolbar
     * @param ilCtrl                    $ctrl
     */
    public function __construct(
        ilSrAccessHandler $access_handler,
        ilGlobalTemplateInterface $global_template,
        ITranslator $translator,
        ilToolbarGUI $toolbar,
        ilCtrl $ctrl
    ) {
        $this->access_handler = $access_handler;
        $this->global_template = $global_template;
        $this->translator = $translator;
        $this->toolbar = $toolbar;
        $this->ctrl = $ctrl;
    }

    /**
     * Renders the assignment toolbar with actions pointing to @see ilSrObjectAssignmentGUI.
     *
     * The toolbar is only added if the current user can manage assignments.
     */
    public function addRoutineAssignmentToolbar() : void
    {
        if (!$this->access_handler->canManageAssignments()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_ASSIGNMENT_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineAssignmentGUI::class,
                ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        $this->render();
    }

    /**
     * Renders the assignment toolbar with actions pointing to @see ilSrRoutineAssignmentGUI.
     *
     * The toolbar is only added if the current user can manage assignments.
     */
    public function addObjectAssignmentToolbar() : void
    {
        if (!$this->access_handler->canManageAssignments()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_ASSIGNMENT_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrObjectAssignmentGUI::class,
                ilSrObjectAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        $this->render();
    }

    /**
     * Renders the routine toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addRoutineToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_ROUTINE_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_ROUTINE_EDIT
            )
        );

        $this->render();
    }

    /**
     * Renders the rule toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addRuleToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_RULE_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrRuleGUI::class,
                ilSrRuleGUI::CMD_RULE_EDIT
            )
        );

        $this->render();
    }

    /**
     * Renders the notification toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addConfirmationToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_CONFIRMATION_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrConfirmationGUI::class,
                ilSrConfirmationGUI::CMD_NOTIFICATION_EDIT
            )
        );

        $this->render();
    }

    /**
     * Renders the reminder toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addReminderToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $this->addPrimaryButton(
            self::ACTION_REMINDER_ADD,
            $this->ctrl->getLinkTargetByClass(
                ilSrReminderGUI::class,
                ilSrReminderGUI::CMD_NOTIFICATION_EDIT
            )
        );

        $this->render();
    }

    /**
     * Adds a primary button to the toolbar.
     *
     * @param string $language_variable
     * @param string $action
     */
    protected function addPrimaryButton(string $language_variable, string $action) : void
    {
        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->translator->txt($language_variable), false);
        $button->setUrl($action);

        $this->toolbar->addButtonInstance($button);
    }

    /**
     * Renders the current toolbar on the current page.
     */
    protected function render() : void
    {
        $this->global_template->setContent($this->toolbar->getHTML());
    }
}