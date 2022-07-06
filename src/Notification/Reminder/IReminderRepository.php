<?php

namespace srag\Plugins\SrLifeCycleManager\Notification\Reminder;

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IReminderRepository extends INotificationRepository
{
    /**
     * Fetches an existing reminder from the database for the given id.
     *
     * @param int $notification_id
     * @return IReminder|null
     */
    public function get(int $notification_id) : ?IReminder;

    /**
     * Fetches all existing reminders from the database that are related
     * to the given routine.
     *
     * NOTE that all reminders are sorted by the days before deletion.
     * This comes in handy when evaluating which notifications to send first.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool     $array_data
     * @return IReminder[]
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array;

    /**
     * Fetches an existing reminder for the given amount of days before deletion
     * from the database that is related to the given routine.
     *
     * NOTE that this method exists because there can only be ONE reminder for
     * the same amount of days before an object's deletion.
     *
     * @param int $routine_id
     * @param int $days_before_deletion
     * @return IReminder|null
     */
    public function getByRoutineAndDaysBeforeDeletion(int $routine_id, int $days_before_deletion) : ?IReminder;

    /**
     * Fetches all reminders from the database that are related to the given
     * routine and were sent to the given object (ref-id).
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return IReminder[]
     */
    public function getSentByRoutineAndObject(IRoutine $routine, int $ref_id) : array;

    /**
     * Creates or updates the given notification in the database.
     *
     * @param IReminder $notification
     * @return IReminder
     */
    public function store(IReminder $notification) : IReminder;

    /**
     * Deletes the given notification from the database.
     *
     * @param IReminder $notification
     * @return bool
     */
    public function delete(IReminder $notification) : bool;

    /**
     * Initializes and returns an empty reminder DTO.
     *
     * @param IRoutine $routine
     * @return IReminder
     */
    public function empty(IRoutine $routine) : IReminder;
}
