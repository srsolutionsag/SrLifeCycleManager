<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrConfirmationTable extends ilSrAbstractNotificationTable
{
    // ilSrConfirmationTable table columns:
    public const COL_CONFIRMATION_EVENT = 'col_confirmation_event';

    /**
     * @inheritDoc
     */
    protected function getTemplateName(): string
    {
        return 'tpl.confirmation_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addNotificationSpecificColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_CONFIRMATION_EVENT));
    }

    /**
     * @inheritDoc
     */
    protected function renderNotificationSpecificColumns(ilTemplate $template, array $data): void
    {
        $template->setVariable(
            self::COL_CONFIRMATION_EVENT,
            $this->translator->txt($data[IConfirmation::F_EVENT])
        );
    }
}
