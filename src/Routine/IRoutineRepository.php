<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineRepository
{
    /**
     * Returns a routine from the database for the given id.
     * Returns null if the given id does not exist.
     *
     * @param int  $routine_id
     * @return IRoutine
     */
    public function get(int $routine_id) : ?IRoutine;

    /**
     * Returns all existing routines. To retrieve routines as array-data
     * true can be passed as an argument (usually required by ilTableGUI).
     *
     * @param bool $array_data
     * @return IRoutine[]|array<int, array>
     */
    public function getAll(bool $array_data = false) : array;

    /**
     * Returns all existing routines that affect the given ref-id.
     *
     * To retrieve routines as array-data true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return IRoutine[]|array<int, array>
     */
    public function getAllByScope(int $ref_id, bool $array_data = false) : array;

    /**
     * Helper function (due to the large constructor) that initializes and
     * returns an empty routine for the given owner and origin.
     *
     * @param int $origin_type
     * @param int $owner_id
     * @return IRoutine
     */
    public function getEmpty(int $origin_type, int $owner_id) : IRoutine;

    /**
     * Returns the closest possible execution date in the future for the
     * execution dates of the given routine-id.
     *
     * @param IRoutine $routine
     * @return DateTime|null
     */
    public function getNextExecutionDate(IRoutine $routine) : ?DateTime;

    /**
     * Creates or updates the given routine in the database.
     *
     * @param IRoutine $routine
     * @return IRoutine
     */
    public function store(IRoutine $routine) : IRoutine;

    /**
     * Deletes an existing routine from the database with all it's
     * relations (manually, as ilias does not support constraints).
     *
     * @param IRoutine $routine
     * @return bool
     */
    public function delete(IRoutine $routine) : bool;
}