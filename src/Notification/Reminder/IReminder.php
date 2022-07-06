<?php

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use DateTimeImmutable;
use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IReminder extends ISentNotification
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

    /**
     * Returns whether the notification is elapsed on the given date.
     *
     * A notification is elapsed if the notified date plus the amount of
     * days before it's submission is past $when.
     *
     * @param DateTimeImmutable|DateTime $when
     * @return bool
     */
    public function isElapsed($when) : bool;
}
