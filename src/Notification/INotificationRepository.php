<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

use DateTime;/**
 * INotificationRepository describes the CRUD operations of a
 * notification repository.
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationRepository
{
    /**
     * Returns an existing notification from the database for the given
     * notification and routine id.
     *
     * @param int $routine_id
     * @param int $notification_id
     * @return IRoutineAwareNotification|null
     */
    public function get(int $routine_id, int $notification_id) : ?IRoutineAwareNotification;

    /**
     * Returns all notifications related to the given routine id.
     *
     * To retrieve routines as array-data true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $routine_id
     * @param bool $array_data
     * @return IRoutineAwareNotification[]|array<int, array>
     */
    public function getAll(int $routine_id, bool $array_data = false) : array;

    /**
     * Returns all notifications that should be executed on the given date.
     *
     * @param DateTime $exec_date
     * @return IRoutineAwareNotification[]
     */
    public function getAllByRoutineExecutionDate(DateTime $exec_date) : array;

    /**
     * @param int $routine_id
     * @return IRoutineAwareNotification
     */
    public function getEmpty(int $routine_id) : IRoutineAwareNotification;

    /**
     * Creates or updates the given notification in the database.
     *
     * @param IRoutineAwareNotification $notification
     * @return IRoutineAwareNotification
     */
    public function store(IRoutineAwareNotification $notification) : IRoutineAwareNotification;

    /**
     * Deletes an existing notification and all it's relations
     * (manually because ilias does not support constraints).
     *
     * @param IRoutineAwareNotification $notification
     * @return bool
     */
    public function delete(IRoutineAwareNotification $notification) : bool;
}