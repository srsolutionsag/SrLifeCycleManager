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
                $this->getActionDropdown(
                    (int) $data[INotification::F_NOTIFICATION_ID],
                    (int) $data[INotification::F_ROUTINE_ID]
                )
            )
        );
    }

    /**
     * @param int $notification_id
     * @param int $routine_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $notification_id, int $routine_id) : Dropdown
    {
        $this->setActionParameters($notification_id, $routine_id);

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_NOTIFICATION_EDIT),
            $this->ctrl->getLinkTargetByClass(
                ilSrNotificationGUI::class,
                ilSrNotificationGUI::CMD_NOTIFICATION_EDIT
            )
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_NOTIFICATION_DELETE),
            $this->ctrl->getLinkTargetByClass(
                ilSrNotificationGUI::class,
                ilSrNotificationGUI::CMD_NOTIFICATION_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $notification_id
     * @param int $routine_id
     * @return void
     */
    protected function setActionParameters(int $notification_id, int $routine_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::PARAM_NOTIFICATION_ID,
            $notification_id
        );
    }
}