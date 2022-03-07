<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;

/**
 * This cron job will delete repository objects that are affected by
 * any of the active routines.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Affected means:
 *
 *         (a)  the object is a child (in the repository tree) of the routines
 *              configured ref-id.
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
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var IDeletableObjectGenerator
     */
    protected $deletable_objects;

    /**
     * @param INotificationSender       $notification_sender
     * @param IDeletableObjectGenerator $deletable_objects
     * @param ResultBuilder             $result_builder
     * @param INotificationRepository   $notification_repository
     * @param IRoutineRepository        $routine_repository
     * @param IWhitelistRepository      $whitelist_repository
     * @param ilLogger                  $logger
     */
    public function __construct(
        INotificationSender $notification_sender,
        IDeletableObjectGenerator $deletable_objects,
        ResultBuilder $result_builder,
        INotificationRepository $notification_repository,
        IRoutineRepository $routine_repository,
        IWhitelistRepository $whitelist_repository,
        ilLogger $logger
    ) {
        parent::__construct($result_builder, $logger);

        $this->notification_repository = $notification_repository;
        $this->routine_repository = $routine_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->notification_sender = $notification_sender;
        $this->deletable_objects = $deletable_objects;
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
        return '...';
    }

    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        foreach ($this->deletable_objects as $object) {
            $object_instance = $object->getInstance();
            $object_ref_id = $object_instance->getRefId();

            foreach ($object->getAffectedRoutines() as $routine) {
                $whitelist_entry = $this->whitelist_repository->get($routine, $object_ref_id);
                $is_whitelisted = (null !== $whitelist_entry);

                // the object must neither be notified nor deleted, because
                // it is opted-out.
                if ($is_whitelisted && $whitelist_entry->isOptOut()) {
                    continue;
                }

                $notifications = $this->notification_repository->getByRoutine($routine);
                $notifications_available = (!empty($notifications));

                // if no notifications are registered and the object is
                // not whitelisted, it can be deleted immediately.
                if ((!$notifications_available) &&
                    (!$is_whitelisted || $whitelist_entry->isElapsed($this->getDate()))
                ) {
                    // delete the object and stop iterating the affected routines,
                    // because the object instance does not exist anymore.
                    $this->deleteObject($object_instance);
                    break;
                }

                // if no notifications are available there's nothing more
                // to be done for the current routine.
                if (!$notifications_available) {
                    continue;
                }

                $sent_notifications = $this->notification_repository->getSentNotifications($routine, $object_ref_id);
                $sent_notification_count = count($sent_notifications);

                // it might be possible that already sent notifications are deleted,
                // therefore we must check if the count is larger-or-equal to the
                // available ones, to check if all have been sent.
                $all_notifications_sent = ($sent_notification_count >= count($notifications));

                $last_notification = (0 < $sent_notification_count) ?
                    $sent_notifications[$sent_notification_count - 1] : null
                ;

                // if notifications are available, the object can only be deleted
                // if all of them have been sent and the last ones execution date
                // is past today.
                if ($all_notifications_sent &&
                    $last_notification &&
                    $last_notification->isElapsed($this->getDate())
                ) {
                    // delete the object and stop iterating the affected routines,
                    // because the object instance does not exist anymore.
                    $this->deleteObject($object_instance);
                    break;
                }

                // if all notification have been sent at this point, there's nothing
                // more to done for the current routine.
                if ($all_notifications_sent) {
                    continue;
                }

                // if $all_notifications_sent is false, the array of notifications
                // is bigger than the array of sent ones. The next array-index of
                // $sent_notifications can be used to determine the next one that
                // must be sent from $notifications.
                //
                // NOTE that this works, due to ordering both notification arrays
                // by their days_before_submission (ASC).
                $next_notification = $notifications[$sent_notification_count];

                // if no notifications were sent, the initial one can be sent without
                // further calculations.
                if (0 === $sent_notification_count) {
                    $this->notifyObject($next_notification, $object_instance);
                    continue;
                }

                // the gap between the last and next notification must be determined,
                // so the notifications don't get "stacked". Notification A with X days
                // before submission should not be appended to Notification Bs Y days,
                // otherwise the object won't be deleted for X+Y days, since notifications
                // are one of the determining factor for deletions.
                $submission_gap = (
                    $last_notification->getDaysBeforeSubmission() -
                    $next_notification->getDaysBeforeSubmission()
                );

                // the notification date can then simply be calculated by adding the
                // determined gap to the last ones date of submission.
                // @todo: this line somehow changes the value returned by $this->getDate().
                $notification_date = $last_notification->getNotifiedDate()->add(
                    new DateInterval("P{$submission_gap}D")
                );

                // send the notification if the notification date is today.
                if ($this->getDate()->format('Y-m-d') === $notification_date->format('Y-m-d')) {
                    $this->notifyObject($next_notification, $object_instance);
                }
            }
        }
    }

    /**
     * Tries to delete the given object, if it fails a corresponding
     * log entry will be made.
     *
     * @param ilObject $object
     * @return void
     */
    protected function deleteObject(ilObject $object) : void
    {
        try {
            $this->info("Deleting object {$object->getRefId()} ({$object->getType()})");
            ilRepUtil::deleteObjects(null, $object->getRefId());
        } catch (ilRepositoryException $exception) {
            $this->error("Could not delete object {$object->getRefId()}: {$exception->getMessage()}");
        }
    }

    /**
     * Sends the given notification to all administrators of the object.
     *
     * @param INotification $notification
     * @param ilObject      $object
     * @return void
     */
    protected function notifyObject(INotification $notification, ilObject $object) : void
    {
        $this->info("Sending administrators of object {$object->getRefId()} notification {$notification->getNotificationId()}");
        $this->notification_sender->sendNotification($notification, $object);
    }

    /**
     * Returns the current datetime object.
     *
     * This function had to be introduced due to PHPUnitTests, which
     * must be able to alter the current date.
     *
     * @return DateTime
     */
    protected function getDate() : DateTime
    {
        return new DateTime();
    }
}