<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineEvent;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObject;
use srag\Plugins\SrLifeCycleManager\Event\EventSubject;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use srag\Plugins\SrLifeCycleManager\ITranslator;

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
     * @var EventSubject
     */
    protected $event_subject;

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
     * @var AffectedObjectProvider
     */
    protected $affected_object_provider;

    /**
     * @var int[]
     */
    protected $deleted_ref_ids = [];

    public function __construct(
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever,
        IRoutineRepository $routine_repository,
        IReminderRepository $notification_repository,
        ITokenRepository $token_repository,
        IWhitelistRepository $whitelist_repository,
        IGeneralRepository $general_repository,
        AffectedObjectProvider $object_provider,
        EventSubject $event_subject,
        ResultBuilder $result_builder,
        INotifier $notifier,
        ilGlobalTemplateInterface $template = null,
    ) {
        parent::__construct($result_builder, $notifier, $template);
        $this->recipient_retriever = $recipient_retriever;
        $this->event_subject = $event_subject;
        $this->routine_repository = $routine_repository;
        $this->reminder_repository = $notification_repository;
        $this->token_repository = $token_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->general_repository = $general_repository;
        $this->notification_sender = $notification_sender;
        $this->affected_object_provider = $object_provider;
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
        foreach ($this->affected_object_provider->getAffectedObjects() as $affected_object) {
            $this->notifier->notifySometimes("Processing routines, still running...");

            if ($this->hasObjectBeenDeleted($affected_object)) {
                continue;
            }

            $ref_id = $affected_object->getObject()->getRefId();
            $routine = $affected_object->getRoutine();

            $whitelist_entry = $this->whitelist_repository->get($routine, $ref_id);

            $deletion_date = (null === $whitelist_entry) ?
                $this->routine_repository->getDeletionDate($routine, $ref_id) :
                $whitelist_entry->getExpiryDate();

            if (null === $deletion_date) {
                // if the deletion date is null at this point, the object has an
                // opt-out whitelist entry, so we continue to the next routine.
                continue;
            }

            if ($deletion_date <= $this->getCurrentDate()) {
                $this->deleteObject($affected_object);
                $this->markObjectAsDeleted($affected_object);
                continue;
            }

            $next_reminder = $this->reminder_repository->getWithDaysBeforeDeletion(
                $routine->getRoutineId(),
                $this->getGap($this->getCurrentDate(), $deletion_date)
            );

            if (null !== $next_reminder) {
                $this->notifyObject($next_reminder, $affected_object);
            }
        }
    }

    /**
     * Tries to delete the given object, if it fails a corresponding
     * log entry will be made.
     */
    protected function deleteObject(AffectedObject $affected_object): void
    {
        $routine = $affected_object->getRoutine();
        $object = $affected_object->getObject();

        // delete object from repository.
        $this->general_repository->deleteObject($object->getRefId());

        // clean up internal data.
        $this->reminder_repository->markObjectAsDeleted($object->getRefId());
        $this->token_repository->delete($object->getRefId());

        // broadcast delete-event.
        $this->event_subject->notify(IRoutineEvent::EVENT_DELETE, $affected_object);

        $this->notifier->notify(
            sprintf(
                "Object %s (%d) deleted by routine %s (%d).",
                $object->getTitle(),
                $object->getRefId(),
                $routine->getTitle(),
                $routine->getRoutineId()
            )
        );
    }

    /**
     * Sends the given notification to all according recipients of the object.
     */
    protected function notifyObject(IReminder $notification, AffectedObject $affected_object): void
    {
        $routine = $affected_object->getRoutine();
        $object = $affected_object->getObject();

        $this->notification_sender->sendNotification($this->recipient_retriever, $notification, $object);

        $this->notifier->notify(
            sprintf(
                'Sent reminder "%s" (%d) from routine "%s" (%d) to object "%s" (%d).',
                $notification->getTitle(),
                $notification->getNotificationId(),
                $routine->getTitle(),
                $routine->getRoutineId(),
                $object->getTitle(),
                $object->getRefId()
            )
        );
    }

    protected function markObjectAsDeleted(AffectedObject $affected_object): void
    {
        $this->deleted_ref_ids[] = $affected_object->getObject()->getRefId();
    }

    protected function hasObjectBeenDeleted(AffectedObject $affected_object): bool
    {
        return in_array($affected_object->getObject()->getRefId(), $this->deleted_ref_ids, true);
    }
}
