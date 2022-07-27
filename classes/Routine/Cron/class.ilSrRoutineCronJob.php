<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Event\IObserver;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This cron job will delete repository objects that are affected by
 * any of the active routines.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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
    /**
     * @var IObserver
     */
    protected $event_observer;

    /**
     * @var IReminderRepository
     */
    protected $reminder_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var DeletableObjectProvider
     */
    protected $object_provider;

    /**
     * @param INotificationSender     $notification_sender
     * @param DeletableObjectProvider $object_provider
     * @param ResultBuilder           $result_builder
     * @param IObserver               $event_observer
     * @param IReminderRepository     $notification_repository
     * @param IRoutineRepository      $routine_repository
     * @param ilLogger                $logger
     */
    public function __construct(
        INotificationSender $notification_sender,
        DeletableObjectProvider $object_provider,
        ResultBuilder $result_builder,
        IObserver $event_observer,
        IReminderRepository $notification_repository,
        IRoutineRepository $routine_repository,
        ilLogger $logger
    ) {
        parent::__construct($result_builder, $logger);

        $this->event_observer = $event_observer;
        $this->reminder_repository = $notification_repository;
        $this->routine_repository = $routine_repository;
        $this->notification_sender = $notification_sender;
        $this->object_provider = $object_provider;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routine Job';
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'This cron job will delete repository objects that are affected by any of the active routines and sends
        notifications (if present) for the configured amount of days before their deletion.';
    }

    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        foreach ($this->object_provider->getDeletableObjects() as $object) {
            $object_instance = $object->getInstance();
            $object_ref_id = $object_instance->getRefId();

            foreach ($object->getAffectingRoutines() as $routine) {
                $notifications = $this->reminder_repository->getByRoutine($routine);

                // if there are no notifications to be sent, the object can be
                // deleted immediately.
                if (empty($notifications)) {
                    $this->deleteObject($routine, $object_instance);
                    break 2;
                }

                $sent_reminders = $this->reminder_repository->getSentByRoutineAndObject($routine, $object_ref_id);
                $sent_reminder_count = count($sent_reminders);

                // it might be possible that already sent notifications are deleted,
                // therefore we must check if the count is larger-or-equal to the
                // available ones, to check if all have been sent.
                $are_all_reminders_sent = ($sent_reminder_count >= count($notifications));

                $last_reminder = (0 < $sent_reminder_count) ?
                    $sent_reminders[$sent_reminder_count - 1] : null
                ;

                // if notifications are available, the object can only be deleted
                // if all of them have been sent and the last ones execution date
                // is past today.
                if ($are_all_reminders_sent &&
                    $last_reminder &&
                    $last_reminder->isElapsed($this->getDate())
                ) {
                    $this->deleteObject($routine, $object_instance);
                    break 2;
                }

                // if all notification have been sent at this point, there's nothing
                // more to done for the current routine.
                if ($are_all_reminders_sent) {
                    continue;
                }

                // if $all_notifications_sent is false, the array of notifications
                // is bigger than the array of sent ones. The next array-index of
                // $sent_notifications can be used to determine the next one that
                // must be sent from $notifications.
                //
                // NOTE that this works, due to ordering both notification arrays
                // by their days_before_deletion (ASC).
                $next_reminder = $notifications[$sent_reminder_count];

                // if no notifications were sent, the initial one can be sent without
                // further calculations.
                if (0 === $sent_reminder_count) {
                    $this->notifyObject($next_reminder, $object_instance);
                    continue;
                }

                // the gap between the last and next notification must be determined,
                // so the notifications don't get "stacked". Notification A with X days
                // before submission should not be appended to Notification Bs Y days,
                // otherwise the object won't be deleted for X+Y days, since notifications
                // are one of the determining factor for deletions.
                $submission_gap = (
                    $last_reminder->getDaysBeforeDeletion() -
                    $next_reminder->getDaysBeforeDeletion()
                );

                // the notification date can then simply be calculated by adding the
                // determined gap to the last ones date of submission.
                $notification_date = $last_reminder->getNotifiedDate()->add(
                    new DateInterval("P{$submission_gap}D")
                );

                // send the notification if the notification date is today.
                if ($this->getDate()->format('Y-m-d') === $notification_date->format('Y-m-d')) {
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
    protected function deleteObject(IRoutine $routine, ilObject $object) : void
    {
        try {
            ilRepUtil::deleteObjects(null, [$object->getRefId()]);
            $this->info("Deleting object {$object->getRefId()} ({$object->getType()})");
            $this->event_observer->broadcast(
                new RoutineEvent(
                    $routine,
                    $object,
                    self::class,
                    RoutineEvent::DELETE
                )
            );
        } catch (ilRepositoryException $exception) {
            $this->error("Could not delete object {$object->getRefId()}: {$exception->getMessage()}");
        }
    }

    /**
     * Sends the given notification to all administrators of the object.
     *
     * @param IReminder $notification
     * @param ilObject  $object
     * @return void
     */
    protected function notifyObject(IReminder $notification, ilObject $object) : void
    {
        $this->notification_sender->sendNotification($notification, $object);
        $this->info("Sending administrators of object {$object->getRefId()} notification {$notification->getNotificationId()}");
    }

    /**
     * Returns the current datetime object.
     *
     * This function had to be introduced due to PHPUnitTests, which
     * must be able to alter the current date.
     *
     * @return DateTimeImmutable
     */
    protected function getDate() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}