<?php

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

use DateTimeImmutable;
use DateTime;

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
    public function getDaysBeforeDeletion() : int;

    /**
     * @param int $amount
     * @return IReminder
     */
    public function setDaysBeforeDeletion(int $amount) : IReminder;
}
