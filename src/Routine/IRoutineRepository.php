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
     * Returns an instance of the whitelist repository.
     *
     * @return IWhitelistRepository
     */
    public function whitelist() : IWhitelistRepository;

    /**
     * Fetches an existing routine from the database for the given id.
     *
     * @param int $routine_id
     * @return IRoutine|null
     */
    public function get(int $routine_id) : ?IRoutine;

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
     * Fetches all active routines from the database that affect the given
     * ref-id and are of the provided routine type.
     *
     * @param int    $ref_id
     * @param string $routine_type
     * @return IRoutine[]
     */
    public function getByRefIdAndType(int $ref_id, string $routine_type) : array;

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