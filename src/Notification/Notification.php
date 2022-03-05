<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification;

use LogicException;
use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Notification implements ISentNotification
{
    /**
     * @var int|null
     */
    protected $notification_id;

    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var int|null
     */
    protected $days_before_submission;

    /**
     * @var int|null
     */
    protected $notified_ref_id;

    /**
     * @var DateTime|null
     */
    protected $notified_date;

    /**
     * @param int           $routine_id
     * @param string        $title
     * @param string        $content
     * @param int|null      $days_before_submission
     * @param int|null      $notification_id
     * @param int|null      $notified_ref_id
     * @param DateTime|null $notified_date
     */
    public function __construct(
        int $routine_id,
        string $title,
        string $content,
        int $days_before_submission = null,
        int $notification_id = null,
        int $notified_ref_id = null,
        DateTime $notified_date = null
    ) {
        $this->routine_id = $routine_id;
        $this->title = $title;
        $this->content = $content;
        $this->days_before_submission = $days_before_submission;
        $this->notification_id = $notification_id;
        $this->notified_ref_id = $notified_ref_id;
        $this->notified_date = $notified_date;
    }

    /**
     * @ineritdoc
     */
    public function getNotificationId() : ?int
    {
        return $this->notification_id;
    }

    /**
     * @ineritdoc
     */
    public function setNotificationId(int $notification_id) : INotification
    {
        $this->notification_id = $notification_id;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getRoutineId() : int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : INotification
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return INotification
     */
    public function setTitle(string $title) : INotification
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @ineritdoc
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @ineritdoc
     */
    public function setContent(string $content) : INotification
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDaysBeforeSubmission() : ?int
    {
        return $this->days_before_submission;
    }

    /**
     * @inheritDoc
     */
    public function setDaysBeforeSubmission(int $amount) : INotification
    {
        $this->days_before_submission = $amount;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotifiedRefId() : int
    {
        if (null === $this->notified_date) {
            throw new LogicException("Notification has not been sent yet.");
        }

        return $this->notified_ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setNotifiedRefId(int $ref_id) : ISentNotification
    {
        $this->notified_ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotifiedDate() : DateTime
    {
        if (null === $this->notified_date) {
            throw new LogicException("Notification has not been sent yet.");
        }

        return $this->notified_date;
    }

    /**
     * @inheritDoc
     */
    public function setNotifiedDate(DateTime $date) : ISentNotification
    {
        $this->notified_date = $date;
        return $this;
    }
}