<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
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
     * @param ilLogger                  $logger
     */
    public function __construct(
        INotificationSender $notification_sender,
        IDeletableObjectGenerator $deletable_objects,
        ResultBuilder $result_builder,
        INotificationRepository $notification_repository,
        IRoutineRepository $routine_repository,
        ilLogger $logger
    ) {
        parent::__construct($result_builder, $logger);

        $this->notification_repository = $notification_repository;
        $this->routine_repository = $routine_repository;
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
                $notifications = $this->notification_repository->getByRoutine($routine);
                $whitelist_entry = $this->routine_repository->whitelist()->get($routine, $object_ref_id);

                // if no notifications are registered and the object is
                // not whitelisted, it can be deleted immediately.
                if (empty($notifications) &&
                    null === $whitelist_entry
                ) {
                    $this->deleteObject($object_instance);
                    break;
                }

                $sent_notifications = $this->notification_repository->getSentNotifications($routine, $object_ref_id);

                $all_notifications_sent = (count($notifications) === count($sent_notifications));
                $is_whitelisted = (null !== $whitelist_entry);
                $is_opted_out = ($is_whitelisted && $whitelist_entry->isOptOut());
                $is_extended = ($is_whitelisted && !$is_opted_out && null !== $whitelist_entry->getElongation());
                $is_elapsed = (
                    $is_extended &&
                    (new DateTime) > $whitelist_entry->getDate()->add(
                        new DateInterval("P{$whitelist_entry->getElongation()}D")
                    )
                );

                // the object must neither be notified nor deleted, because
                // it is opted-out.
                if ($is_whitelisted && $is_opted_out) {
                    break;
                }

                // if all notifications were sent, the object can be deleted if
                //      (a) the object is not whitelisted, or
                //      (b) the object was an extension that has been elapsed.
                if ($all_notifications_sent &&
                    (!$is_whitelisted || (!$is_opted_out && $is_extended && $is_elapsed))
                ) {
                    $this->deleteObject($object_instance);
                    break;
                }

                // both notification arrays were ordered by days before submission,
                // therefore the next one can be determined by the size of the sent
                // ones, whereas the count - 1 is the next index.
                $next_notification = $notifications[count($sent_notifications) - 1];
                $this->notifyObject($next_notification, $object_instance);
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
        $this->notification_sender->sendNotification($notification, $object);
    }
}