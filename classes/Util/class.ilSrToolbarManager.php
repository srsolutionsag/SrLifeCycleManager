<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * This class is responsible for managing the plugin toolbars.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This helper class is meant to centralize the toolbar implementation
 * and simplify their management. Methods that add or render toolbars
 * must never be fluently, because there should only be exactly ONE
 * toolbar on every page.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrToolbarManager
{
    // ilSrToolbarManager toolbar actions:
    public const ACTION_ROUTINE_ADD = 'action_routine_add';
    public const ACTION_NOTIFICATION_ADD = 'action_notification_add';
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
     * Renders the routine toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addRoutineToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->translator->txt(self::ACTION_ROUTINE_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::CMD_ROUTINE_EDIT
        ));

        $this->toolbar->addButtonInstance($button);
        $this->global_template->setContent(
            $this->toolbar->getHTML()
        );
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

        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->translator->txt(self::ACTION_RULE_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::CMD_RULE_EDIT
        ));

        $this->toolbar->addButtonInstance($button);
        $this->global_template->setContent(
            $this->toolbar->getHTML()
        );
    }

    /**
     * Renders the notification toolbar to the current page (global template).
     *
     * The toolbar is only added if the current user can manage routines.
     */
    public function addNotificationToolbar() : void
    {
        if (!$this->access_handler->canManageRoutines()) {
            return;
        }

        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->translator->txt(self::ACTION_NOTIFICATION_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::CMD_NOTIFICATION_EDIT
        ));

        $this->toolbar->addButtonInstance($button);
        $this->global_template->setContent(
            $this->toolbar->getHTML()
        );
    }
}