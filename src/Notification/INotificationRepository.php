<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * Interface INotificationRepository
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationRepository
{
    /**
     * @param int $notification_id
     * @return Notification|null
     */
    public function get(int $notification_id) : ?Notification;

    /**
     * @param INotification $notification
     * @return Notification
     */
    public function store(INotification $notification) : Notification;

    /**
     * @param INotification $notification
     * @return bool
     */
    public function delete(INotification $notification) : bool;
}