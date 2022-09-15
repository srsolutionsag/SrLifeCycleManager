<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationRepository
{
    /**
     * Returns whether the given notification (regardless of the type) has already
     * been sent to the given object (ref-id).
     *
     * @param INotification $notification
     * @param int           $ref_id
     * @return bool
     */
    public function hasObjectBeenNotified(INotification $notification, int $ref_id) : bool;

    /**
     * Creates a relation between the given notification (regardless of the type)
     * and given object (ref-id), which marks the notification as sent.
     *
     * @param INotification $notification
     * @param int           $ref_id
     * @return ISentNotification
     */
    public function markObjectAsNotified(INotification $notification, int $ref_id) : ISentNotification;

    /**
     * Retrieves the sent-information of the given notification for the given object
     * (ref-id).
     *
     * @param INotification $notification
     * @param int           $ref_id
     * @return ISentNotification|null
     */
    public function getSentInformation(INotification $notification, int $ref_id) : ?ISentNotification;

    /**
     * Removes the relation between any notification and the given object (ref-id),
     * which marks the object as deleted (and not notified anymore).
     *
     * @param int $ref_id
     */
    public function markObjectAsDeleted(int $ref_id) : void;
}
