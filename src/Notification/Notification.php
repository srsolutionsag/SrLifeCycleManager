<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Notification implements INotification
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
     * @var int
     */
    protected $days_before_submission;

    /**
     * @param int      $routine_id
     * @param string   $title
     * @param string   $content
     * @param int      $days_before_submission
     * @param int|null $notification_id
     */
    public function __construct(
        int $routine_id,
        string $title,
        string $content,
        int $days_before_submission,
        int $notification_id = null
    ) {
        $this->routine_id = $routine_id;
        $this->title = $title;
        $this->content = $content;
        $this->days_before_submission = $days_before_submission;
        $this->notification_id = $notification_id;
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
    public function getDaysBeforeSubmission() : int
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
}