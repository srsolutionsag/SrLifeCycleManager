<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\Confirmation;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrConfirmationRepository extends ilSrAbstractNotificationRepository implements IConfirmationRepository
{
    /**
     * @inheritDoc
     */
    public function get(int $notification_id): ?IConfirmation
    {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   confirmation.event
                FROM srlcm_confirmation AS confirmation
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = confirmation.notification_id
                WHERE confirmation.notification_id = %s
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
    public function getByRoutine(IRoutine $routine, bool $array_data = false): array
    {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   confirmation.event
                FROM srlcm_confirmation AS confirmation
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = confirmation.notification_id
                WHERE notification.routine_id = %s
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [$routine->getRoutineId() ?? 0]
                )
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRoutineAndEvent(
        int $routine_id,
        string $event,
        bool $array_data = false
    ): ?IConfirmation {
        $query = "
            SELECT notification.notification_id, notification.routine_id, notification.title, notification.content, 
                   confirmation.event
                FROM srlcm_confirmation AS confirmation
                LEFT JOIN srlcm_notification AS notification ON notification.notification_id = confirmation.notification_id
                WHERE notification.routine_id = %s
                AND confirmation.event = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'text'],
                    [
                        $routine_id,
                        $event,
                    ]
                )
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IConfirmation $notification): IConfirmation
    {
        if (null !== $notification->getNotificationId()) {
            return $this->updateNotification($notification);
        }

        return $this->insertNotification($notification);
    }

    /**
     * @inheritDoc
     */
    public function delete(IConfirmation $notification): bool
    {
        $query = "
            DELETE notification, confirmation
                FROM srlcm_confirmation AS confirmation
                INNER JOIN srlcm_notification AS notification ON confirmation.notification_id = notification.notification_id
                WHERE confirmation.notification_id = %s
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
    public function empty(IRoutine $routine): IConfirmation
    {
        return new Confirmation(
            $routine->getRoutineId() ?? 0,
            '',
            '',
            ''
        );
    }

    protected function updateNotification(IConfirmation $notification): IConfirmation
    {
        $query = "
            UPDATE srlcm_confirmation AS confirmation
                INNER JOIN srlcm_notification AS notification ON confirmation.notification_id = notification.notification_id
                SET notification.title = %s, notification.content = %s, confirmation.event = %s
                WHERE confirmation.notification_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['text', 'text', 'text', 'integer'],
            [
                $notification->getTitle(),
                $notification->getContent(),
                $notification->getEvent(),
                $notification->getNotificationId(),
            ]
        );

        return $notification;
    }

    protected function insertNotification(IConfirmation $notification): IConfirmation
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
                $notification->getEvent(),
            ]
        );

        $confirmation_query = "
            INSERT INTO srlcm_confirmation (notification_id, event)
                VALUES (%s, %s)
            ;
        ";

        $this->database->manipulateF(
            $confirmation_query,
            ['integer', 'text'],
            [
                $notification_id,
                $notification->getEvent(),
            ]
        );

        $notification->setNotificationId($notification_id);

        return $notification;
    }

    /**
     * @inheritDoc
     */
    protected function transformToDTO(array $query_result): Confirmation
    {
        return new Confirmation(
            (int) $query_result[IConfirmation::F_ROUTINE_ID],
            $query_result[IConfirmation::F_TITLE],
            $query_result[IConfirmation::F_CONTENT],
            $query_result[IConfirmation::F_EVENT],
            (int) $query_result[IConfirmation::F_NOTIFICATION_ID],
            $this->getNotifiedRefId($query_result),
            $this->getNotifiedDate($query_result)
        );
    }
}
