<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationRepository
{
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
     * Returns whether the given notification (regardless of the type) has already
     * been sent to the given object (ref-id).
     *
     * @param INotification $notification
     * @param int           $ref_id
     * @return bool
     */
    public function hasObjectBeenNotified(INotification $notification, int $ref_id) : bool;
}
