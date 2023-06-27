<?php

declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractNotificationTable extends ilSrAbstractTable
{
    // ilSrNotificationTable table columns:
    public const COL_NOTIFICATION_TITLE = 'col_notification_title';
    public const COL_NOTIFICATION_CONTENT = 'col_notification_content';

    // ilSrNotificationTable actions:
    public const ACTION_NOTIFICATION_EDIT = 'action_notification_edit';
    public const ACTION_NOTIFICATION_DELETE = 'action_notification_delete';

    /**
     * @inheritDoc
     */
    protected function addTableColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_NOTIFICATION_TITLE));
        $this->addColumn($this->translator->txt(self::COL_NOTIFICATION_CONTENT));

        $this->addNotificationSpecificColumns();

        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data): void
    {
        $template->setVariable(self::COL_NOTIFICATION_TITLE, $data[INotification::F_TITLE]);
        $template->setVariable(self::COL_NOTIFICATION_CONTENT, $data[INotification::F_CONTENT]);

        $this->renderNotificationSpecificColumns($template, $data);

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
    protected function getActionDropdown(int $notification_id, int $routine_id): Dropdown
    {
        $this->setActionParameters($notification_id, $routine_id);

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_NOTIFICATION_EDIT),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_gui),
                ilSrAbstractNotificationGUI::CMD_NOTIFICATION_EDIT
            )
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_NOTIFICATION_DELETE),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_gui),
                ilSrAbstractNotificationGUI::CMD_NOTIFICATION_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $notification_id
     * @param int $routine_id
     * @return void
     */
    protected function setActionParameters(int $notification_id, int $routine_id): void
    {
        $this->ctrl->setParameterByClass(
            get_class($this->parent_gui),
            ilSrAbstractNotificationGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            get_class($this->parent_gui),
            ilSrAbstractNotificationGUI::PARAM_NOTIFICATION_ID,
            $notification_id
        );
    }

    /**
     * This method is called when rendering each table-row and should fill all
     * necessary template variables that are specific to the notification.
     *
     * @param ilTemplate $template
     * @param array      $data
     * @return void
     */
    abstract protected function renderNotificationSpecificColumns(ilTemplate $template, array $data): void;

    /**
     * This method is called when initializing this table and should add all
     * necessary template variables that are specific to the notification.
     */
    abstract protected function addNotificationSpecificColumns(): void;
}
