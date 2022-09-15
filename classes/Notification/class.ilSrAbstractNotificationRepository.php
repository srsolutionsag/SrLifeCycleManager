<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * This repository is responsible for all notification CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractNotificationRepository implements INotificationRepository
{
    use DateTimeHelper;
    use DTOHelper;

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
    public function hasObjectBeenNotified(INotification $notification, int $ref_id) : bool
    {
        $query = "
            SELECT COUNT(*) AS count
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

        $count = (int) $results[0]['count'];

        return (1 >= $count);
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
    public function markObjectAsDeleted(int $ref_id): void
    {
        $query = "DELETE FROM srlcm_notified_objects WHERE ref_id = %s";

        $this->database->manipulateF(
            $query,
            ['integer'],
            [$ref_id]
        );
    }

    /**
     * @inheritDoc
     */
    public function getSentInformation(INotification $notification, int $ref_id) : ?ISentNotification
    {
        $query = "
            SELECT date FROM srlcm_notified_objects
                WHERE notification_id = %s
                AND routine_id = %s
                AND ref_id = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer', 'integer'],
                    [
                        $notification->getNotificationId(),
                        $notification->getRoutineId(),
                        $ref_id,
                    ]
                )
            )
        );
    }

    /**
     * @param INotification $notification
     * @param int           $ref_id
     * @return ISentNotification
     */
    protected function updateObjectReference(INotification $notification, int $ref_id) : ISentNotification
    {
        $query = "
            UPDATE srlcm_notified_objects
                SET date = %s
                WHERE notification_id = %s
                AND routine_id = %s
                AND ref_id = %s
            ;
        ";

        $today = $this->getCurrentDate();

        $this->database->manipulateF(
            $query,
            ['date', 'integer', 'integer', 'integer'],
            [
                $this->getMysqlDateString($today),
                $notification->getNotificationId(),
                $notification->getRoutineId(),
                $ref_id,
            ]
        );

        /** @var $notification ISentNotification */
        return $notification->setNotifiedDate($today);
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

        $today = $this->getCurrentDate();

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'date'],
            [
                $notification->getRoutineId(),
                $notification->getNotificationId(),
                $ref_id,
                $this->getMysqlDateString($today),
            ]
        );

        return $notification
            ->setNotifiedRefId($ref_id)
            ->setNotifiedDate($today);
    }

    /**
     * @param array<string, string> $query_result
     * @return DateTimeImmutable|null
     */
    protected function getNotifiedDate(array $query_result) : ?DateTimeImmutable
    {
        return $this->getDateByQueryResult($query_result, ISentNotification::F_NOTIFIED_DATE);
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