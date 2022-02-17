<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * Notification DTO
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Notification implements IRoutineAwareNotification
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
     * @var int
     */
    protected $relation_id;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $days_before_submission;

    /**
     * @param string   $message
     * @param int      $days_before_submission
     * @param int      $routine_id
     * @param int|null $notification_id
     */
    public function __construct(
        string $message,
        int $days_before_submission,
        int $routine_id,
        int $notification_id = null
    ) {
        $this->notification_id = $notification_id;
        $this->message = $message;
        $this->routine_id = $routine_id;
        $this->days_before_submission = $days_before_submission;
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
     * @ineritdoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @ineritdoc
     */
    public function setMessage(string $message) : INotification
    {
        $this->message = $message;
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
    public function getRelationId() : int
    {
        return $this->relation_id;
    }

    /**
     * @inheritDoc
     */
    public function setRelationId(int $relation_id) : IRoutineAwareNotification
    {
        $this->relation_id = $relation_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : IRoutineAwareNotification
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDaysBeforeSubmission() : int
    {
        return $this->days_before_submission;
    }

    /**
     * @inheritDoc
     */
    public function setDaysBeforeSubmission(int $days) : IRoutineAwareNotification
    {
        $this->days_before_submission = $days;
        return $this;
    }
}