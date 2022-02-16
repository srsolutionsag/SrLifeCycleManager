<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationTable extends ilSrAbstractTable
{
    protected const COL_NOTIFICATION_MESSAGE = 'col_notification_message';
    protected const COL_NOTIFICATION_DAYS    = 'col_notification_days';

    /**
     * @inheritDoc
     */
    protected function getTableColumns() : array
    {
        return [
            self::COL_NOTIFICATION_MESSAGE,
            '',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void
    {
        $template->setVariable(strtoupper(self::COL_NOTIFICATION_MESSAGE), $row_data[ilSrNotification::F_MESSAGE]);
        $template->setVariable(strtoupper(self::COL_NOTIFICATION_DAYS), $row_data[ilSrRoutineNotification::F_DAYS_BEFORE_SUBMISSION]);
    }
}