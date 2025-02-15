<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification;

use DateTimeImmutable;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class Notification implements ISentNotification
{
    /**
     * @param int                    $routine_id
     * @param string                 $title
     * @param string                 $content
     * @param int|null               $notification_id
     * @param int|null               $notified_ref_id
     * @param DateTimeImmutable|null $notified_date
     */
    public function __construct(
        protected int $routine_id,
        protected string $title,
        protected string $content,
        protected ?int $notification_id = null,
        protected ?int $notified_ref_id = null,
        protected ?\DateTimeImmutable $notified_date = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getNotificationId(): ?int
    {
        return $this->notification_id;
    }

    /**
     * @inheritDoc
     */
    public function setNotificationId(int $notification_id): INotification
    {
        $this->notification_id = $notification_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId(): int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id): INotification
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotifiedRefId(): int
    {
        $this->abortIfNotSent();

        return $this->notified_ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setNotifiedRefId(int $ref_id): ISentNotification
    {
        $this->notified_ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotifiedDate(): DateTimeImmutable
    {
        $this->abortIfNotSent();

        return $this->notified_date;
    }

    /**
     * @inheritDoc
     */
    public function setNotifiedDate(DateTimeImmutable $date): ISentNotification
    {
        $this->notified_date = $date;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasBeenSent(): bool
    {
        return (null !== $this->notified_ref_id && null !== $this->notified_date);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): INotification
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function setContent(string $content): INotification
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Helper function that throws an exception if the notification
     * has not been sent yet.
     */
    protected function abortIfNotSent(): void
    {
        if (!$this->hasBeenSent()) {
            throw new LogicException(
                "Notification ({$this->getNotificationId()}) has not been sent to {$this->getNotifiedRefId()} yet"
            );
        }
    }
}
