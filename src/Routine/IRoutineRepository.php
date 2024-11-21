<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTimeImmutable;

/**
 * Describes the CRUD operations of a routine.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineRepository
{
    /**
     * Fetches an existing routine from the database for the given id.
     *
     * @param int $routine_id
     * @return IRoutine|null
     */
    public function get(int $routine_id): ?IRoutine;

    /**
     * Fetches all existing routines from the database.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAll(bool $array_data = false): array;

    /**
     * Fetches all existing routines from the database that affect the
     * given ref-id.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAllByRefId(int $ref_id, bool $array_data = false): array;

    /**
     * Returns all existing routines from the database that are affecting
     * the given object (ref-id and type).
     *
     * If the routine has an opt-out whitelist entry it will not be considered.
     *
     * @param int    $ref_id
     * @param string $type
     * @return IRoutine[]
     */
    public function getAllForComparison(int $ref_id, string $type): array;

    /**
     * Fetches all existing routines from the database that ARE NOT already
     * affecting the given object (ref-id).
     *
     * Affecting means, the object is either assigned directly, or the routine
     * of a parent is recursive.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAllUnassigned(int $ref_id, bool $array_data = false): array;

    /**
     * Calculates and returns the deletion date of the given routine and object
     * (ref-id) by considering the registered reminders.
     *
     * NOTE that this method DOES NOT check if there is an active whitelist entry,
     * so please consider this beforehand if required.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return DateTimeImmutable
     */
    public function getDeletionDate(IRoutine $routine, int $ref_id): DateTimeImmutable;

    /**
     * Creates or updates the given routine in the database.
     *
     * @param IRoutine $routine
     * @return IRoutine
     */
    public function store(IRoutine $routine): IRoutine;

    /**
     * Deletes the given routine from the database and all related entries.
     *
     * @param IRoutine $routine
     * @return bool
     */
    public function delete(IRoutine $routine): bool;

    /**
     * Initializes and returns an empty routine object.
     *
     * @param int $owner_id
     * @param int $origin_type
     * @return IRoutine
     */
    public function empty(int $owner_id, int $origin_type): IRoutine;
}
