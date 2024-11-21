<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Notification;

use DateTimeImmutable;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ISentNotification extends INotification
{
    // ISentNotification attributes:
    public const F_NOTIFIED_REF_ID = 'ref_id';
    public const F_NOTIFIED_DATE = 'date';

    /**
     * @return int
     * @throws LogicException if the notification has not been sent yet.
     */
    public function getNotifiedRefId(): int;

    /**
     * @param int $ref_id
     * @return ISentNotification
     */
    public function setNotifiedRefId(int $ref_id): ISentNotification;

    /**
     * @return DateTimeImmutable
     * @throws LogicException if the notification has not been sent yet.
     */
    public function getNotifiedDate(): DateTimeImmutable;

    /**
     * @param DateTimeImmutable $date
     * @return ISentNotification
     */
    public function setNotifiedDate(DateTimeImmutable $date): ISentNotification;

    /**
     * @return bool
     */
    public function hasBeenSent(): bool;
}
