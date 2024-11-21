<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IReminder extends ISentReminder
{
    // IReminder attributes
    public const F_DAYS_BEFORE_DELETION = 'days_before_deletion';

    /**
     * @return int
     */
    public function getDaysBeforeDeletion(): int;

    /**
     * @param int $amount
     * @return IReminder
     */
    public function setDaysBeforeDeletion(int $amount): IReminder;
}
