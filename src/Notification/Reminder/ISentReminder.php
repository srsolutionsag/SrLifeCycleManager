<?php

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use DateTimeImmutable;
use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ISentReminder extends ISentNotification
{
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
