<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\IRoutineNotificationRelation;

use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationTable extends ilSrAbstractTable
{
    protected const COL_NOTIFICATION_MESSAGE = 'col_notification_message';
    protected const COL_NOTIFICATION_DAYS    = 'col_notification_days';
    protected const COL_NOTIFICATION_ACTIONS = 'col_actions';

    /**
     * @inheritDoc
     */
    protected function getTableColumns() : array
    {
        return [
            self::COL_NOTIFICATION_DAYS,
            self::COL_NOTIFICATION_MESSAGE,
            '',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void
    {
        $template->setVariable(strtoupper(self::COL_NOTIFICATION_DAYS), $row_data[IRoutineNotificationRelation::F_DAYS_BEFORE_SUBMISSION]);
        $template->setVariable(strtoupper(self::COL_NOTIFICATION_MESSAGE), $row_data[INotification::F_MESSAGE]);
        $template->setVariable(strtoupper(self::COL_NOTIFICATION_ACTIONS),
            $this->ui->renderer()->render($this->getActionDropdown(
                (int) $row_data[IRoutineNotificationRelation::F_ROUTINE_ID],
                (int) $row_data[IRoutineNotificationRelation::F_NOTIFICATION_ID]
            ))
        );
    }

    /**
     * returns an action dropdown for each notification row-entry.
     *
     * @param int $routine_id
     * @param int $notification_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $routine_id, int $notification_id) : Dropdown
    {
        $this->setActionParameters($routine_id, $notification_id);

        return $this->ui->factory()->dropdown()->standard([
            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrNotificationGUI::ACTION_NOTIFICATION_EDIT),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_NOTIFICATION_EDIT
                )
            ),

            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrNotificationGUI::ACTION_NOTIFICATION_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_NOTIFICATION_DELETE
                )
            ),
        ]);
    }

    /**
     * Sets the given routine and notification id for each class a link is built
     * to in @see ilSrNotificationTable::getActionDropdown().
     *
     * Note that this method MUST be called before links are
     * generated so that the links have the correct target.
     *
     * @param int $routine_id
     * @param int $notification_id
     */
    protected function setActionParameters(int $routine_id, int $notification_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::QUERY_PARAM_NOTIFICATION_ID,
            $notification_id
        );
    }
}