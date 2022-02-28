<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Describes the CRUD operations of a notification.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationRepository
{
    /**
     * Fetches an existing notification from the database for the given id.
     *
     * @param int $notification_id
     * @return INotification|null
     */
    public function get(int $notification_id) : ?INotification;

    /**
     * Fetches all existing notifications from the database that are related
     * to the given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool     $array_data
     * @return array
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array;

    /**
     * Creates or updates a given notification in the database.
     *
     * @param INotification $notification
     * @return INotification
     */
    public function store(INotification $notification) : INotification;

    /**
     * Deletes the given notification from the database.
     *
     * @param INotification $notification
     * @return bool
     */
    public function delete(INotification $notification) : bool;

    /**
     * Initializes and returns an empty notification object.
     *
     * @param IRoutine $routine
     * @return INotification
     */
    public function empty(IRoutine $routine) : INotification;
}