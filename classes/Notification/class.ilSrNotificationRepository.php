<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrNotificationRepository implements INotificationRepository
{
    /**
     * @var string mysql datetime format string.
     */
    protected const MYSQL_DATETIME_FORMAT = 'Y-m-d';

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(int $notification_id) : ?INotification
    {
        $query = "
            SELECT notification_id, routine_id, title, content, days_before_submission
                FROM srlcm_notification
                WHERE notification_id = %s
            ; 
        ";

        $result = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [$notification_id]
            )
        );

        if (!empty($result)) {
            return $this->transformToNotification($result[0]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array
    {
        $query = "
            SELECT notification_id, routine_id, title, content, days_before_submission
                FROM srlcm_notification
                WHERE routine_id = %s
                ORDER BY days_before_submission ASC
            ; 
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [$routine->getRoutineId()]
            )
        );

        if ($array_data) {
            return $results;
        }

        $notifications = [];
        foreach ($results as $query_result) {
            $notifications[] = $this->transformToNotification($query_result);
        }

        return $notifications;
    }

    /**
     * @inheritDoc
     */
    public function getSentNotifications(IRoutine $routine, int $ref_id) : array
    {
        $query = "
            SELECT msg.notification_id, msg.routine_id, msg.title, msg.content, msg.days_before_submission
                FROM srlcm_notification AS msg
                JOIN srlcm_notified_objects AS notified_objs ON notified_objs.notification_id = msg.notification_id
                WHERE notified_objs.routine_id = %s
                AND notified_objs.ref_id = %s
                ORDER BY msg.days_before_submission ASC
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer', 'integer'],
                [
                    $routine->getRoutineId(),
                    $ref_id
                ]
            )
        );

        $sent_notifications = [];
        foreach ($results as $result) {
            $sent_notifications[] = $this->transformToSentNotification($result);
        }

        return $sent_notifications;
    }

    /**
     * @inheritDoc
     */
    public function store(INotification $notification) : INotification
    {
        if (null !== $notification->getNotificationId()) {
            return $this->updateNotification($notification);
        }

        return $this->insertNotification($notification);
    }

    /**
     * @inheritDoc
     */
    public function delete(INotification $notification) : bool
    {
        $query = "DELETE FROM srlcm_notification WHERE notification_id = %s;";

        $this->database->manipulateF(
            $query,
            ['integer'],
            [$notification->getNotificationId()]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function empty(IRoutine $routine) : INotification
    {
        return new Notification(
            $routine->getRoutineId(),
            '',
            ''
        );
    }

    /**
     * @param INotification $notification
     * @return INotification
     */
    protected function updateNotification(INotification $notification) : INotification
    {
        $query = "
            UPDATE srlcm_notification SET
                title = %s,
                content = %s,
                days_before_submission = %s
                WHERE notification_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['text', 'text', 'integer', 'integer'],
            [
                $notification->getTitle(),
                $notification->getContent(),
                $notification->getDaysBeforeSubmission(),
                $notification->getNotificationId(),
            ]
        );

        return $notification;
    }

    /**
     * @param INotification $notification
     * @return INotification
     */
    protected function insertNotification(INotification $notification) : INotification
    {
        $query = "
            INSERT INTO srlcm_notification (notification_id, routine_id, title, content, days_before_submission)
                VALUES (%s, %s, %s, %s, %s)    
            ;
        ";

        $notification_id = (int) $this->database->nextId('srlcm_notification');
        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'text', 'text', 'integer'],
            [
                $notification_id,
                $notification->getRoutineId(),
                $notification->getTitle(),
                $notification->getContent(),
                $notification->getDaysBeforeSubmission(),
            ]
        );

        return $notification->setNotificationId((int) $notification_id);
    }

    /**
     * @param array $query_result
     * @return INotification
     */
    protected function transformToNotification(array $query_result) : INotification
    {
        return new Notification(
            (int) $query_result[INotification::F_ROUTINE_ID],
            $query_result[INotification::F_TITLE],
            $query_result[INotification::F_CONTENT],
            (int) $query_result[INotification::F_DAYS_BEFORE_SUBMISSION],
            (int) $query_result[INotification::F_NOTIFICATION_ID]
        );
    }

    /**
     * @param array $query_result
     * @return ISentNotification
     */
    protected function transformToSentNotification(array $query_result) : ISentNotification
    {
        return new Notification(
            (int) $query_result[INotification::F_ROUTINE_ID],
            $query_result[INotification::F_TITLE],
            $query_result[INotification::F_CONTENT],
            (int) $query_result[INotification::F_DAYS_BEFORE_SUBMISSION],
            (int) $query_result[INotification::F_NOTIFICATION_ID],
            (int) $query_result[ISentNotification::F_NOTIFIED_REF_ID],
            DateTime::createFromFormat(
                self::MYSQL_DATETIME_FORMAT,
                $query_result[ISentNotification::F_NOTIFIED_DATE]
            )
        );
    }
}