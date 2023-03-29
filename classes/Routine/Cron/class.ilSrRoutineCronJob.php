<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\Observer;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;

/**
 * This cron job will delete repository objects that are affected by
 * any of the active routines.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Affected means:
 *
 *         (a)  the object is a direct child (in the repository tree) of the routines
 *              assigned ref-id OR the routine is recursive and the object is one of
 *              the children.
 *         (b)  the object is of the same type as the routines configured
 *              routine type.
 *         (c)  the objects attributes are all applicable to all the related
 *              rules of the routine.
 *
 * NOTE that an object can be affected by more than one routine, in
 * which case the object will be deleted if only one of them affects
 * it.
 *
 * To enable flexibility in regard to this cron jobs actions, all
 * operations that "do something" are in separate functions that
 * could be overwritten by derived classes.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineCronJob extends ilSrAbstractCronJob
{
    use DateTimeHelper;

    /**
     * @var IRecipientRetriever
     */
    protected $recipient_retriever;

    /**
     * @var Observer
     */
    protected $event_observer;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IReminderRepository
     */
    protected $reminder_repository;

    /**
     * @var ITokenRepository
     */
    protected $token_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var IGeneralRepository
     */
    protected $general_repository;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var DeletableObjectProvider
     */
    protected $object_provider;

    public function __construct(
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever,
        DeletableObjectProvider $object_provider,
        ResultBuilder $result_builder,
        Observer $event_observer,
        IRoutineRepository $routine_repository,
        IReminderRepository $notification_repository,
        ITokenRepository $token_repository,
        IWhitelistRepository $whitelist_repository,
        IGeneralRepository $general_repository,
        ilLogger $logger
    ) {
        parent::__construct($result_builder, $logger);

        $this->recipient_retriever = $recipient_retriever;
        $this->event_observer = $event_observer;
        $this->routine_repository = $routine_repository;
        $this->reminder_repository = $notification_repository;
        $this->token_repository = $token_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->general_repository = $general_repository;
        $this->notification_sender = $notification_sender;
        $this->object_provider = $object_provider;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'LifeCycleManager Routine Job';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'This cron job will delete repository objects that are affected by any of the active routines and sends
        notifications (if present) for the configured amount of days before their deletion.';
    }

    /**
     * @inheritDoc
     */
    protected function execute(): void
    {
        foreach ($this->object_provider->getDeletableObjects() as $object) {
            $object_instance = $object->getInstance();
            $object_ref_id = $object_instance->getRefId();

            foreach ($object->getAffectingRoutines() as $routine) {
                $whitelist_entry = $this->whitelist_repository->get($routine, $object_ref_id);

                $deletion_date = (null === $whitelist_entry) ?
                    $this->routine_repository->getDeletionDate($routine, $object_ref_id) :
                    $whitelist_entry->getExpiryDate();

                if (null === $deletion_date) {
                    // if the deletion date is null at this point, the object has an
                    // opt-out whitelist entry, so we continue to the next routine.
                    continue;
                }

                if ($deletion_date <= $this->getCurrentDate()) {
                    $this->deleteObject($routine, $object_instance);
                    // continue to the next object since this one no longer exists.
                    continue 2;
                }

                $next_reminder = $this->reminder_repository->getWithDaysBeforeDeletion(
                    $routine->getRoutineId(),
                    $this->getGap($this->getCurrentDate(), $deletion_date)
                );

                if (null !== $next_reminder) {
                    $this->notifyObject($next_reminder, $object_instance);
                }
            }
        }
    }

    /**
     * Tries to delete the given object, if it fails a corresponding
     * log entry will be made.
     *
     * @param IRoutine $routine
     * @param ilObject $object
     * @return void
     */
    protected function deleteObject(IRoutine $routine, ilObject $object): void
    {
        // delete object from repository.
        $this->info("Deleting object {$object->getRefId()} ({$object->getType()})");
        $this->general_repository->deleteObject($object->getRefId());

        // clean up internal data.
        $this->reminder_repository->markObjectAsDeleted($object->getRefId());
        $this->token_repository->delete($object->getRefId());

        // broadcast delete-event.
        $this->event_observer->broadcast(
            new RoutineEvent(
                $routine,
                $object,
                RoutineEvent::EVENT_DELETE
            )
        );
    }

    /**
     * Sends the given notification to all administrators of the object.
     *
     * @param IReminder $notification
     * @param ilObject  $object
     * @return void
     */
    protected function notifyObject(IReminder $notification, ilObject $object): void
    {
        $this->info(
            "Sending administrators of object {$object->getRefId()} notification {$notification->getNotificationId()}"
        );

        $this->notification_sender->sendNotification($this->recipient_retriever, $notification, $object);
    }
}
