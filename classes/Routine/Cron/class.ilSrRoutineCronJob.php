<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineEvent;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObject;
use srag\Plugins\SrLifeCycleManager\Event\EventSubject;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

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
     * @var int[]
     */
    protected array $deleted_ref_ids = [];

    public function __construct(
        protected INotificationSender $notification_sender,
        protected IRecipientRetriever $recipient_retriever,
        protected IRoutineRepository $routine_repository,
        protected IReminderRepository $reminder_repository,
        protected ITokenRepository $token_repository,
        protected IWhitelistRepository $whitelist_repository,
        protected IGeneralRepository $general_repository,
        protected AffectedObjectProvider $affected_object_provider,
        protected EventSubject $event_subject,
        ResultBuilder $result_builder,
        INotifier $notifier,
        ilGlobalTemplateInterface $template = null
    ) {
        parent::__construct($result_builder, $notifier, $template);
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
