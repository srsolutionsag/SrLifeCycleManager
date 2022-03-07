<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Notification;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ISentNotification extends INotification
{
    // ISentNotification attribute names:
    public const F_NOTIFIED_REF_ID = 'ref_id';
    public const F_NOTIFIED_DATE = 'date';

    /**
     * @return int
     */
    public function getNotifiedRefId() : int;

    /**
     * @param int $ref_id
     * @return ISentNotification
     */
    public function setNotifiedRefId(int $ref_id) : ISentNotification;

    /**
     * @return DateTime
     */
    public function getNotifiedDate() : Datetime;

    /**
     * @param DateTime $date
     * @return ISentNotification
     */
    public function setNotifiedDate(DateTime $date) : ISentNotification;

    /**
     * Returns whether the notification is elapsed on the given date.
     *
     * A notification is elapsed if the notified date plus the amount of
     * days before it's submission is past $when.
     *
     * @param DateTime $when
     * @return bool
     */
    public function isElapsed(DateTime $when) : bool;
}