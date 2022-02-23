<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Notification\IRoutineNotificationRelation;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationRepository implements INotificationRepository
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @param IRoutineRepository $routine_repository
     * @param ilDBInterface      $database
     */
    public function __construct(IRoutineRepository $routine_repository, ilDBInterface $database)
    {
        $this->routine_repository = $routine_repository;
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id, int $notification_id) : ?IRoutineAwareNotification
    {
        /** @var $ar_notification ilSrNotification|null */
        $ar_notification = ilSrNotification::find($notification_id);
        if (null === $ar_notification) {
            return null;
        }

        /** @var $ar_relation ilSrRoutineNotification */
        $ar_relation = $this->getRelationList($routine_id, $notification_id)->first();

        return $this->transformToDTO($ar_notification, $ar_relation);
    }

    /**
     * @inheritDoc
     */
    public function getAll(int $routine_id, bool $array_data = false) : array
    {
        /** @var $ar_relations ilSrRoutineNotification[] */
        $ar_relations = ilSrRoutineNotification::where([
            IRoutineNotificationRelation::F_ROUTINE_ID => $routine_id,
        ], '=')->get();

        $notifications = [];
        foreach ($ar_relations as $ar_relation) {
            /** @var $ar_notification ilSrNotification|null */
            $ar_notification = ilSrNotification::find($ar_relation->getNotificationId());
            if (null === $ar_notification) {
                continue;
            }

            $notifications[] = ($array_data) ?
                $this->transformToArray($ar_notification, $ar_relation) :
                $this->transformToDTO($ar_notification, $ar_relation)
            ;
        }

        return $notifications;
    }

    /**
     * @todo: add to interface
     *
     * @param IRoutine $routine
     * @return array|null
     */
    public function getAllByRoutine(IRoutine $routine) : ?array
    {
        $exec_date = $this->routine_repository->getNextExecutionDate($routine);
        if (null === $exec_date) {
            return null;
        }

        $notification_table = ilSrNotification::TABLE_NAME;
        $routine_table = ilSrRoutine::TABLE_NAME;
        $relation_table = ilSrRoutineNotification::TABLE_NAME;
        $exec_date = $exec_date->format(ilSrRoutine::MYSQL_DATE_FORMAT);

        $query = "
            SELECT msg.message, rel.notification_id, rel.routine_id, rel.days_before_submission 
                FROM $notification_table AS msg
                JOIN $relation_table AS rel ON rel.notification_id = msg.notification_id
                JOIN $routine_table AS routine ON routine.routine_id = rel.routine_id
                WHERE routine.active = 1
                AND CURDATE() >= DATE_ADD('$exec_date', INTERVAL rel.days_before_submission DAY)
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        if (empty($results)) {
            return null;
        }

        $notifications = [];
        foreach ($results as $result) {
            $notifications[] = new Notification(
                $result[INotification::F_MESSAGE],
                $result[IRoutineNotificationRelation::F_DAYS_BEFORE_SUBMISSION],
                $result[IRoutineNotificationRelation::F_ROUTINE_ID],
                $result[IRoutineNotificationRelation::F_NOTIFICATION_ID]
            );
        }

        return $notifications;
    }

    /**
     * @inheritDoc
     */
    public function getEmpty(int $routine_id) : IRoutineAwareNotification
    {
        return new Notification(
            '',
            0,
            $routine_id
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutineAwareNotification $notification) : IRoutineAwareNotification
    {
        $ar_notification = (null !== $notification->getNotificationId()) ?
            (ilSrNotification::find($notification->getNotificationId()) ?? new ilSrNotification()) :
            new ilSrNotification()
        ;

        $ar_notification->setMessage($notification->getMessage());
        $ar_notification->store();

        $ar_relations = $this->getRelationList(
            $notification->getRoutineId(),
            $ar_notification->getNotificationId()
        );

        /** @var $ar_relation ilSrRoutineNotification */
        if (empty($ar_relations->get())) {
            $ar_relation = new ilSrRoutineNotification();
            $ar_relation
                ->setNotificationId($ar_notification->getNotificationId())
                ->setRoutineId($notification->getRoutineId())
                ->setDaysBeforeSubmission($notification->getDaysBeforeSubmission())
                ->store()
            ;
        } else {
            $ar_relation = $ar_relations->first();
        }

        return $this->transformToDTO($ar_notification, $ar_relation);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutineAwareNotification $notification) : bool
    {
        // there's nothing to do if the DTO hasn't been stored yet.
        if (null === $notification->getNotificationId()) {
            return true;
        }

        $ar_notification = ilSrNotification::find($notification->getNotificationId());
        if (null !== $ar_notification) {
            $ar_relations = $this->getRelationList(
                $notification->getRoutineId(),
                $notification->getNotificationId()
            );

            foreach ($ar_relations->get() as $ar_relation) {
                $ar_relation->delete();
            }

            $ar_notification->delete();
            return true;
        }

        return false;
    }

    /**
     * Helper function that transforms the notification and relation
     * data into a DTO.
     *
     * @param INotification                $ar_notification
     * @param IRoutineNotificationRelation $ar_relation
     * @return IRoutineAwareNotification
     */
    protected function transformToDTO(
        INotification $ar_notification,
        IRoutineNotificationRelation $ar_relation
    ) : IRoutineAwareNotification
    {
        return new Notification(
            $ar_notification->getMessage(),
            $ar_relation->getDaysBeforeSubmission(),
            $ar_relation->getRoutineId(),
            $ar_notification->getNotificationId()
        );
    }

    /**
     * Helper function that returns the ar list of the routine-notification
     * relations.
     *
     * @param int $routine_id
     * @param int $notification_id
     * @return ActiveRecordList
     */
    protected function getRelationList(int $routine_id, int $notification_id) : ActiveRecordList
    {
        return ilSrRoutineNotification::where([
            IRoutineNotificationRelation::F_ROUTINE_ID => $routine_id,
            IRoutineNotificationRelation::F_NOTIFICATION_ID => $notification_id,
        ], '=');
    }

    /**
     * Helper function that transforms the notification and relation
     * data into array-data.
     *
     * @param INotification                $ar_notification
     * @param IRoutineNotificationRelation $ar_relation
     * @return array<string, mixed>
     */
    protected function transformToArray(
        INotification $ar_notification,
        IRoutineNotificationRelation $ar_relation
    ) : array
    {
        return [
            IRoutineNotificationRelation::F_NOTIFICATION_ID => $ar_notification->getNotificationId(),
            IRoutineNotificationRelation::F_ROUTINE_ID => $ar_relation->getRoutineId(),
            IRoutineNotificationRelation::F_DAYS_BEFORE_SUBMISSION => $ar_relation->getDaysBeforeSubmission(),
            INotification::F_MESSAGE => $ar_notification->getMessage(),
        ];
    }
}