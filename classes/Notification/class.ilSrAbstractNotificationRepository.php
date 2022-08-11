<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractNotificationRepository implements INotificationRepository
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
    public function markObjectAsNotified(INotification $notification, int $ref_id) : ISentNotification
    {
        if ($this->hasObjectBeenNotified($notification, $ref_id)) {
            return $this->updateObjectReference($notification, $ref_id);
        }

        return $this->insertObjectReference($notification, $ref_id);
    }

    /**
     * @inheritDoc
     */
    public function hasObjectBeenNotified(INotification $notification, int $ref_id) : bool
    {
        $query = "
            SELECT reference.routine_id, reference.ref_id, reference.date, reference.notification_id,
                   notification.title, notification.content
                FROM srlcm_notified_objects AS reference
                INNER JOIN srlcm_notification AS notification ON reference.notification_id = notification.notification_id
                WHERE reference.notification_id = %s
                AND reference.routine_id = %s
                AND reference.ref_id = %s
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer', 'integer', 'integer'],
                [
                    $notification->getNotificationId(),
                    $notification->getRoutineId(),
                    $ref_id,
                ]
            )
        );

        return (!empty($results));
    }

    /**
     * @param INotification $notification
     * @param int           $ref_id
     * @return ISentNotification
     */
    protected function updateObjectReference(INotification $notification, int $ref_id) : ISentNotification
    {
        $query = "
            UPDATE srlcm_notified_objects SET date = %s
                WHERE notification_id = %s
                AND routine_id = %s
                AND ref_id = %s
            ;
        ";

        $now = new DateTimeImmutable();

        $this->database->manipulateF(
            $query,
            ['date', 'integer', 'integer', 'integer'],
            [
                $now->format(self::MYSQL_DATETIME_FORMAT),
                $notification->getNotificationId(),
                $notification->getRoutineId(),
                $ref_id,
            ]
        );

        /** @var $notification ISentNotification */
        return $notification->setNotifiedDate($now);
    }

    /**
     * @param INotification $notification
     * @param int           $ref_id
     * @return ISentNotification
     */
    protected function insertObjectReference(INotification $notification, int $ref_id) : ISentNotification
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

        return $notification
            ->setNotifiedRefId($ref_id)
            ->setNotifiedDate($now);
    }

    /**
     * @param array<string, string> $query_result
     * @return DateTimeImmutable|null
     */
    protected function getNotifiedDate(array $query_result) : ?DateTimeImmutable
    {
        $notified_date = (isset($query_result[ISentNotification::F_NOTIFIED_DATE])) ?
            DateTimeImmutable::createFromFormat(
                self::MYSQL_DATETIME_FORMAT,
                $query_result[ISentNotification::F_NOTIFIED_DATE]
            ) : null;

        if (false === $notified_date) {
            throw new LogicException("Could not create datetime object from mysql format.");
        }

        return $notified_date;
    }

    /**
     * @param array<string, string> $query_result
     * @return int|null
     */
    protected function getNotifiedRefId(array $query_result) : ?int
    {
        return (isset($query_result[ISentNotification::F_NOTIFIED_REF_ID])) ?
            (int) $query_result[ISentNotification::F_NOTIFIED_REF_ID] : null;
    }
}