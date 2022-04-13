<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrNotificationRepository implements INotificationRepository
{
    use DTOHelper;

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

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [$notification_id]
                )
            )
        );
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

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $routine->getRoutineId() ?? 0,
                    ]
                )
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRoutineAndDaysBeforeSubmission(int $routine_id, int $days_before_submission) : ?INotification
    {
        $query = "
            SELECT notification_id, routine_id, title, content, days_before_submission
                FROM srlcm_notification
                WHERE routine_id = %s
                AND days_before_submission = %s
            ; 
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer'],
                    [
                        $routine_id,
                        $days_before_submission,
                    ]
                )
            )
        );
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
                    $routine->getRoutineId() ?? 0,
                    $ref_id
                ]
            )
        );

        $sent_notifications = [];
        foreach ($results as $result) {
            $sent_notifications[] = $this->transformToSentNotificationDTO($result);
        }

        return $sent_notifications;
    }

    /**
     * @inheritDoc
     */
    public function notifyObject(INotification $notification, int $ref_id) : ISentNotification
    {
        $query = "
            INSERT INTO srlcm_notified_objects (routine_id, notification_id, ref_id, date)
                VALUES (%s, %s, %s, %s)
            ;
        ";

        $now = new DateTimeImmutable();
        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'date'],
            [
                $notification->getRoutineId(),
                $notification->getNotificationId(),
                $ref_id,
                $now->format(self::MYSQL_DATETIME_FORMAT),
            ]
        );

        /** @var $notification ISentNotification */
        return $notification
            ->setNotifiedRefId($ref_id)
            ->setNotifiedDate($now)
        ;
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
            '',
            0
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
    protected function transformToDTO(array $query_result) : INotification
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
    protected function transformToSentNotificationDTO(array $query_result) : ISentNotification
    {
        return new Notification(
            (int) $query_result[INotification::F_ROUTINE_ID],
            $query_result[INotification::F_TITLE],
            $query_result[INotification::F_CONTENT],
            (int) $query_result[INotification::F_DAYS_BEFORE_SUBMISSION],
            (int) $query_result[INotification::F_NOTIFICATION_ID],
            (int) $query_result[ISentNotification::F_NOTIFIED_REF_ID],
            DateTimeImmutable::createFromFormat(
                self::MYSQL_DATETIME_FORMAT,
                $query_result[ISentNotification::F_NOTIFIED_DATE]
            )
        );
    }
}