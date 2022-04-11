<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * This class is responsible for managing the plugin tabs.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This helper class is meant to centralize the tabs implementation
 * and simplify their management. All methods in this class (except
 * any additional getters) should be fluent (return this instance).
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrTabManager
{
    // ilSrTabManager tab name and ids:
    public const TAB_CONFIG   = 'tab_config_index';
    public const TAB_ROUTINES = 'tab_routine_index';

    // ilSrTabManager language variables:
    protected const MSG_BACK_TO = 'msg_back_to';

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilSrAccessHandler $access_handler
     * @param ITranslator       $translator
     * @param ilTabsGUI         $tabs
     * @param ilCtrl            $ctrl
     */
    public function __construct(
        ilSrAccessHandler $access_handler,
        ITranslator $translator,
        ilTabsGUI $tabs,
        ilCtrl $ctrl
    ) {
        $this->access_handler = $access_handler;
        $this->translator = $translator;
        $this->tabs = $tabs;
        $this->ctrl = $ctrl;
    }

    /**
     * Adds a tab pointing to @see ilSrConfigGUI::CMD_INDEX.
     *
     * If true is provided as an argument, this tab will be shown as active.
     *
     * @param bool $is_active
     * @return self
     */
    public function addConfigurationTab(bool $is_active = false) : self
    {
        // add plugin-configuration tab only for administrator
        if (!$this->access_handler->isAdministrator()) {
            return $this;
        }

        $this->tabs->addTab(
            self::TAB_CONFIG,
            $this->translator->txt(self::TAB_CONFIG),
            $this->ctrl->getLinkTargetByClass(
                ilSrConfigGUI::class,
                ilSrConfigGUI::CMD_INDEX
            )
        );

        if ($is_active) {
            $this->setActiveTab(self::TAB_CONFIG);
        }

        return $this;
    }

    /**
     * Adds a tab pointing to @see ilSrRoutineGUI::CMD_INDEX.
     *
     * If true is provided as an argument, this tab will be shown as active.
     *
     * @param bool $is_active
     * @return self
     */
    public function addRoutineTab(bool $is_active = false) : self
    {
        // add routine-tab only for routine managers.
        if (!$this->access_handler->canManageRoutines()) {
            return $this;
        }

        $this->tabs->addTab(
            self::TAB_ROUTINES,
            $this->translator->txt(self::TAB_ROUTINES),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_INDEX
            )
        );

        if ($is_active) {
            $this->setActiveTab(self::TAB_ROUTINES);
        }

        return $this;
    }

    /**
     * Sets the current back-to target to point to @see ilSrRoutineGUI::index().
     *
     * @return self
     */
    public function addBackToRoutines() : self
    {
        $this->setBackToTarget(
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_INDEX
            )
        );

        return $this;
    }

    /**
     * Adds or overrides the back-to link shown in front of the tabs.
     *
     * @param string $target
     * @return self
     */
    public function setBackToTarget(string $target) : self
    {
        $this->tabs->setBackTarget(
            $this->translator->txt(self::MSG_BACK_TO),
            $target
        );

        return $this;
    }

    /**
     * Shows a given tab-id as activated (can only be one at a time).
     *
     * @param string $tab_id
     * @return self
     */
    public function setActiveTab(string $tab_id) : self
    {
        $this->tabs->activateTab($tab_id);
        return $this;
    }

    /**
     * Deactivates all activated tabs by setting an invalid character as id.
     *
     * @return $this
     */
    public function deactivateTabs() : self
    {
        $this->setActiveTab('§');
        return $this;
    }
}