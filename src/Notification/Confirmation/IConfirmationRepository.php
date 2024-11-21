<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfirmationRepository extends INotificationRepository
{
    /**
     * Fetches an existing confirmation from the database for the given id.
     *
     * @param int $notification_id
     * @return IConfirmation|null
     */
    public function get(int $notification_id): ?IConfirmation;

    /**
     * Fetches all existing confirmations from the database that are related
     * to the given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool     $array_data
     * @return IConfirmation[]
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false): array;

    /**
     * Fetches an existing confirmation for the given event from the database that
     * is related to the given routine.
     *
     * NOTE that this method exists because there can only be ONE confirmation for
     * the same routine event.
     *
     * @param int    $routine_id
     * @param string $event
     * @return IConfirmation|null
     */
    public function getByRoutineAndEvent(int $routine_id, string $event): ?IConfirmation;

    /**
     * Creates or updates the given notification in the database.
     *
     * @param IConfirmation $notification
     * @return IConfirmation
     */
    public function store(IConfirmation $notification): IConfirmation;

    /**
     * Deletes the given notification from the database.
     *
     * @param IConfirmation $notification
     * @return bool
     */
    public function delete(IConfirmation $notification): bool;

    /**
     * Initializes and returns an empty Confirmation DTO.
     *
     * @param IRoutine $routine
     * @return IConfirmation
     */
    public function empty(IRoutine $routine): IConfirmation;
}
