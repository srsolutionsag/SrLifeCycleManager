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
     * @return INotification|null
     */
    public function get(int $notification_id) : ?INotification;

    /**
     * @param INotification $notification
     * @return INotification
     */
    public function store(INotification $notification) : INotification;

    /**
     * @param INotification $notification
     * @return bool
     */
    public function delete(INotification $notification) : bool;

    /**
     * transforms an ActiveRecord instance to a DTO.
     *
     * @param INotification $ar_notification
     * @return INotification
     */
    public function transformToDTO(INotification $ar_notification) : INotification;

    /**
     * transforms an ActiveRecord instance to a array-data.
     *
     * @param INotification $ar_notification
     * @return array<int, array>
     */
    public function transformToArray(INotification $ar_notification) : array;
}