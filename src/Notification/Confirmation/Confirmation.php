<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Confirmation extends Notification implements IConfirmation
{
    /**
     * @var string
     */
    protected $event;

    /**
     * @param int                    $routine_id
     * @param string                 $title
     * @param string                 $content
     * @param string                 $event
     * @param int|null               $notification_id
     * @param int|null               $notified_ref_id
     * @param DateTimeImmutable|null $notified_date
     */
    public function __construct(
        int $routine_id,
        string $title,
        string $content,
        string $event,
        ?int $notification_id = null,
        ?int $notified_ref_id = null,
        ?DateTimeImmutable $notified_date = null
    ) {
        parent::__construct($routine_id, $title, $content, $notification_id, $notified_ref_id, $notified_date);
        $this->event = $event;
    }

    /**
     * @inheritDoc
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function setEvent(string $event): IConfirmation
    {
        $this->event = $event;
        return $this;
    }
}
