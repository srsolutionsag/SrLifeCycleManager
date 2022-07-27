<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\Reminder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrReminderRepository extends ilSrAbstractNotificationRepository implements IReminderRepository
{
    /**
     * @inheritDoc
     */
    public function get(int $notification_id) : ?IReminder
    {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   reminder.days_before_deletion
                FROM srlcm_reminder AS reminder
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = reminder.notification_id
                WHERE reminder.notification_id = %s
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
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   reminder.days_before_deletion
                FROM srlcm_reminder AS reminder
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = reminder.notification_id
                WHERE notification.routine_id = %s
                ORDER BY reminder.days_before_deletion
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [$routine->getRoutineId() ?? 0]
                )
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRoutineAndDaysBeforeDeletion(
        int $routine_id,
        int $days_before_deletion,
        bool $array_data = false
    ) : ?IReminder {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   reminder.days_before_deletion
                FROM srlcm_reminder AS reminder
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = reminder.notification_id
                WHERE notification.routine_id = %s
                AND reminder.days_before_deletion = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer'],
                    [
                        $routine_id,
                        $days_before_deletion,
                    ]
                )
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getSentByRoutineAndObject(IRoutine $routine, int $ref_id) : array
    {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   reminder.days_before_deletion, reference.ref_id, reference.date
                FROM srlcm_reminder AS reminder
                INNER JOIN srlcm_notification AS notification ON notification.notification_id = reminder.notification_id
                INNER JOIN srlcm_notified_objects AS reference ON reference.notification_id = reminder.notification_id
                WHERE reference.routine_id = %s
                AND reference.ref_id = %s
                ORDER BY reminder.days_before_deletion
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer'],
                    [
                        $routine->getRoutineId() ?? 0,
                        $ref_id
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IReminder $notification) : IReminder
    {
        if (null !== $notification->getNotificationId()) {
            return $this->updateNotification($notification);
        }

        return $this->insertNotification($notification);
    }

    /**
     * @inheritDoc
     */
    public function delete(IReminder $notification) : bool
    {
        $query = "
            DELETE notification, reminder
                FROM srlcm_reminder AS reminder
                INNER JOIN srlcm_notification AS notification ON reminder.notification_id = notification.notification_id
                WHERE reminder.notification_id = %s
            ;
        ";

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
    public function empty(IRoutine $routine) : IReminder
    {
        return new Reminder(
            $routine->getRoutineId() ?? 0,
            '',
            '',
            0
        );
    }

    /**
     * @param IReminder $notification
     * @return IReminder
     */
    protected function updateNotification(IReminder $notification) : IReminder
    {
        $query = "
                UPDATE srlcm_reminder AS reminder
                INNER JOIN srlcm_notification AS notification ON reminder.notification_id = notification.notification_id
                SET notification.title = %s, notification.content = %s, reminder.days_before_deletion = %s
                WHERE reminder.notification_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['text', 'text', 'integer', 'integer'],
            [
                $notification->getTitle(),
                $notification->getContent(),
                $notification->getDaysBeforeDeletion(),
                $notification->getNotificationId(),
            ]
        );

        return $notification;
    }

    /**
     * @param IReminder $notification
     * @return IReminder
     */
    protected function insertNotification(IReminder $notification) : IReminder
    {
        $notification_query = "
            INSERT INTO srlcm_notification (notification_id, routine_id, title, content)
                VALUES (%s, %s, %s, %s)    
            ;
        ";

        $notification_id = (int) $this->database->nextId('srlcm_notification');
        $this->database->manipulateF(
            $notification_query,
            ['integer', 'integer', 'text', 'text', 'text'],
            [
                $notification_id,
                $notification->getRoutineId(),
                $notification->getTitle(),
                $notification->getContent(),
                $notification->getDaysBeforeDeletion(),
            ]
        );

        $reminder_query = "
            INSERT INTO srlcm_reminder (notification_id, days_before_deletion)
                VALUES (%s, %s)
            ;
        ";

        $this->database->manipulateF(
            $reminder_query,
            ['integer', 'integer'],
            [
                $notification_id,
                $notification->getDaysBeforeDeletion(),
            ]
        );

        $notification->setNotificationId($notification_id);

        return $notification;
    }

    /**
     * @inheritDoc
     */
    protected function transformToDTO(array $query_result)
    {
        return new Reminder(
            (int) $query_result[IReminder::F_ROUTINE_ID],
            $query_result[IReminder::F_TITLE],
            $query_result[IReminder::F_CONTENT],
            (int) $query_result[IReminder::F_DAYS_BEFORE_DELETION],
            (int) $query_result[IReminder::F_NOTIFICATION_ID],
            $this->getNotifiedRefId($query_result),
            $this->getNotifiedDate($query_result)
        );
    }
}