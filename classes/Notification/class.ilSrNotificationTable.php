<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrNotificationTable extends ilSrAbstractTable
{
    // ilSrNotificationTable table columns:
    public const COL_NOTIFICATION_TITLE = 'col_notification_title';
    public const COL_NOTIFICATION_CONTENT = 'col_notification_content';
    public const COL_NOTIFICATION_DAYS_BEFORE_SUBMISSION = 'col_notification_days_before_submission';

    // ilSrNotificationTable actions:
    public const ACTION_NOTIFICATION_EDIT = 'action_notification_edit';
    public const ACTION_NOTIFICATION_DELETE = 'action_notification_delete';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param Factory           $ui_factory
     * @param Renderer          $renderer
     * @param ITranslator       $translator
     * @param ilSrAccessHandler $access_handler
     * @param ilCtrl            $ctrl
     * @param IRoutine          $routine
     * @param object            $parent_gui_object
     * @param string            $parent_gui_cmd
     * @param array             $table_data
     */
    public function __construct(
        Factory $ui_factory,
        Renderer $renderer,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilCtrl $ctrl,
        IRoutine $routine,
        object $parent_gui_object,
        string $parent_gui_cmd,
        array $table_data
    ) {
        parent::__construct(
            $ui_factory, $renderer, $translator, $access_handler, $ctrl, $parent_gui_object, $parent_gui_cmd, $table_data
        );

        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.notification_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_NOTIFICATION_TITLE));
        $this->addColumn($this->translator->txt(self::COL_NOTIFICATION_CONTENT));
        $this->addColumn($this->translator->txt(self::COL_NOTIFICATION_DAYS_BEFORE_SUBMISSION));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        $template->setVariable(self::COL_NOTIFICATION_TITLE, $data[INotification::F_TITLE]);
        $template->setVariable(self::COL_NOTIFICATION_CONTENT, $data[INotification::F_CONTENT]);
        $template->setVariable(self::COL_NOTIFICATION_DAYS_BEFORE_SUBMISSION, $data[INotification::F_DAYS_BEFORE_SUBMISSION]);
        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->getActionDropdown($data[INotification::F_NOTIFICATION_ID])
            )
        );
    }

    /**
     * @param int $notification_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $notification_id) : Dropdown
    {
        $this->setActionParameters($notification_id);

        $actions = [];

        // these actions are only necessary if the user is administrator
        // or the owner of the current routine.
        if ($this->access_handler->isRoutineOwner($this->routine->getOwnerId())) {
            $actions[] = $this->ui->factory()->button()->shy(
                $this->plugin->txt(self::ACTION_NOTIFICATION_EDIT),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_NOTIFICATION_EDIT
                )
            );

            $actions[] = $this->ui->factory()->button()->shy(
                $this->plugin->txt(self::ACTION_NOTIFICATION_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_NOTIFICATION_DELETE
                )
            );
        }

        return $this->ui->factory()->dropdown()->standard($actions);
    }

    /**
     * @param int $notification_id
     * @return void
     */
    protected function setActionParameters(int $notification_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::PARAM_ROUTINE_ID,
            $this->routine->getRoutineId()
        );

        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::PARAM_NOTIFICATION_ID,
            $notification_id
        );
    }
}