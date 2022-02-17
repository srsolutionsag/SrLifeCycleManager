<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * INotification describes the DAO of a notification.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotification
{
    public const F_NOTIFICATION_ID = 'notification_id';
    public const F_MESSAGE = 'message';

    /**
     * @return int|null
     */
    public function getNotificationId() : ?int;

    /**
     * @param int $notification_id
     * @return INotification
     */
    public function setNotificationId(int $notification_id) : INotification;

    /**
     * @return string
     */
    public function getMessage() : string;

    /**
     * @param string $message
     * @return INotification
     */
    public function setMessage(string $message) : INotification;
}