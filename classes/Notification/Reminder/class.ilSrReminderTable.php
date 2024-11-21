<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrReminderTable extends ilSrAbstractNotificationTable
{
    // ilSrReminderTable table columns:
    public const COL_REMINDER_DAYS_BEFORE_DELETION = 'col_reminder_days_before_deletion';

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.reminder_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addNotificationSpecificColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_REMINDER_DAYS_BEFORE_DELETION));
    }

    /**
     * @inheritDoc
     */
    protected function renderNotificationSpecificColumns(ilTemplate $template, array $data) : void
    {
        $template->setVariable(
            self::COL_REMINDER_DAYS_BEFORE_DELETION,
            $data[IReminder::F_DAYS_BEFORE_DELETION]
        );
    }
}
