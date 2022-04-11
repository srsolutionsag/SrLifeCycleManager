<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

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
    public function get(int $routine_id) : ?IRoutine;

    /**
     * Fetches all existing routines from the database.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAll(bool $array_data = false) : array;

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
    public function getAllByRefId(int $ref_id, bool $array_data = false) : array;

    /**
     * Fetches all existing routines from the database that affect the
     * given ref-id AND are active.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return array
     */
    public function getAllActiveByRefId(int $ref_id, bool $array_data = false) : array;

    /**
     * Fetches all existing assignments that are not assigned or already
     * affecting the given object (ref-id).
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAllUnassignedByRefId(int $ref_id, bool $array_data = false) : array;

    /**
     * Creates or updates the given routine in the database.
     *
     * @param IRoutine $routine
     * @return IRoutine
     */
    public function store(IRoutine $routine) : IRoutine;

    /**
     * Deletes the given routine from the database and all related entries.
     *
     * @param IRoutine $routine
     * @return bool
     */
    public function delete(IRoutine $routine) : bool;

    /**
     * Initializes and returns an empty routine object.
     *
     * @param int $owner_id
     * @param int $origin_type
     * @return IRoutine
     */
    public function empty(int $owner_id, int $origin_type) : IRoutine;
}