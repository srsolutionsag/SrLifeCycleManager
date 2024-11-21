<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

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
     * Fetches the next reminder that needs to be sent for the given routine.
     * If a previous reminder is provided, it returns the next in order.
     *
     * @param IRoutine       $routine
     * @param IReminder|null $previous_reminder
     * @return IReminder|null
     */
    public function getNextReminder(IRoutine $routine, IReminder $previous_reminder = null) : ?IReminder;

    /**
     * Fetches the most recently sent reminder which is related to the given routine
     * and sent to the given object (ref-id).
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return IReminder|null
     */
    public function getRecentlySent(IRoutine $routine, int $ref_id) : ?IReminder;

    /**
     * Fetches an existing reminder with less than the given amount of days before
     * deletion from the database that is related to the given routine.
     *
     * NOTE that the result will be ordered by days before deletion DESC (e.g. 3 to 1).
     *
     * @param IRoutine $routine
     * @param int      $days_before_deletion
     * @return IReminder[]
     */
    public function getWithLessDaysBeforeDeletion(IRoutine $routine, int $days_before_deletion) : array;

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
    public function getWithDaysBeforeDeletion(int $routine_id, int $days_before_deletion) : ?IReminder;

    /**
     * Fetches the first or earliest reminder that needs to be sent for the
     * given routine.
     *
     * The earliest is considered to be the one with the highest amount of
     * days before deletion.
     *
     * @param IRoutine $routine
     * @return IReminder|null
     */
    public function getFirstByRoutine(IRoutine $routine) : ?IReminder;

    /**
     * Fetches the last or latest reminder that needs to be sent for the
     * given routine.
     *
     * The latest is considered to be the one with the highest amount of
     * days before deletion.
     *
     * @param IRoutine $routine
     * @return IReminder|null
     */
    public function getLastByRoutine(IRoutine $routine) : ?IReminder;

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
