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
        IReminderRepository $notification_repository,
        ITokenRepository $token_repository,
        IWhitelistRepository $whitelist_repository,
        IGeneralRepository $general_repository,
        ilLogger $logger
    ) {
        parent::__construct($result_builder, $logger);

        $this->recipient_retriever = $recipient_retriever;
        $this->event_observer = $event_observer;
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
                $reminders = $this->reminder_repository->getByRoutine($routine);

                if (null === $whitelist_entry) {
                    $previously_sent_reminder = $this->reminder_repository->getRecentlySent($routine, $object_ref_id);

                    // NOTE: this reminder will not contain information about being sent.
                    // $last_reminder->isElapsed() cannot be used.
                    $last_reminder = $this->reminder_repository->getLastByRoutine($routine);

                    $has_last_reminder_been_sent = (
                        null !== $previously_sent_reminder &&
                        null !== $last_reminder &&
                        $previously_sent_reminder->getNotificationId() === $last_reminder->getNotificationId()
                    );

                    // if there are reminders and the last one has been sent, the object
                    // can be deleted if the reminder is elapsed today.
                    if (empty($reminders) ||
                        ($has_last_reminder_been_sent && $previously_sent_reminder->isElapsed($this->getCurrentDate()))
                    ) {
                        $this->deleteObject($routine, $object_instance);
                        continue 2;
                    }

                    $next_reminder = $this->reminder_repository->getNextReminder($routine, $previously_sent_reminder);

                    // send the reminder straight away if it's the first one.
                    if (null === $previously_sent_reminder && null !== $next_reminder) {
                        $this->notifyObject($next_reminder, $object_instance);
                    }

                    // send the reminder only for the correct amount of days before
                    // deletion if there are already sent reminders.
                    if (null !== $previously_sent_reminder && null !== $next_reminder) {
                        $previous_interval = new DateInterval("P{$previously_sent_reminder->getDaysBeforeDeletion()}D");
                        $deletion_date = $previously_sent_reminder->getNotifiedDate()->add($previous_interval);

                        if ($next_reminder->getDaysBeforeDeletion() === $this->getGap($this->getCurrentDate(), $deletion_date)) {
                            $this->notifyObject($next_reminder, $object_instance);
                        }
                    }

                    continue;
                }

                // if there is no reminder or the whitelist entry is expired
                // the object can be deleted today.
                if ($whitelist_entry->isExpired($this->getCurrentDate())) {
                    $this->deleteObject($routine, $object_instance);
                    continue 2;
                }

                // get a reminder for the current amount of days before deletion
                // (expiry date in this scenario).
                $next_reminder = $this->reminder_repository->getWithDaysBeforeDeletion(
                    $routine->getRoutineId(),
                    $this->getGap($this->getCurrentDate(), $whitelist_entry->getExpiryDate())
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
