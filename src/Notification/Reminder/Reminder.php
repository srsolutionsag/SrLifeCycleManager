<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use DateTimeImmutable;
use DateInterval;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Reminder extends Notification implements IReminder
{
    /**
     * @var int
     */
    protected $days_before_deletion;

    /**
     * @param int                    $routine_id
     * @param string                 $title
     * @param string                 $content
     * @param int                    $days_before_deletion
     * @param int|null               $notification_id
     * @param int|null               $notified_ref_id
     * @param DateTimeImmutable|null $notified_date
     */
    public function __construct(
        int $routine_id,
        string $title,
        string $content,
        int $days_before_deletion,
        ?int $notification_id = null,
        ?int $notified_ref_id = null,
        ?DateTimeImmutable $notified_date = null
    ) {
        parent::__construct($routine_id, $title, $content, $notification_id, $notified_ref_id, $notified_date);
        $this->days_before_deletion = $days_before_deletion;
    }

    /**
     * @inheritDoc
     */
    public function getDaysBeforeDeletion(): int
    {
        return $this->days_before_deletion;
    }

    /**
     * @inheritDoc
     */
    public function setDaysBeforeDeletion(int $amount): IReminder
    {
        $this->days_before_deletion = $amount;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isElapsed($when): bool
    {
        if (!$this->hasBeenSent()) {
            return false;
        }

        $elapsed_date = $this->getNotifiedDate()->add(new DateInterval("P{$this->getDaysBeforeDeletion()}D"));

        return ($when > $elapsed_date);
    }
}
