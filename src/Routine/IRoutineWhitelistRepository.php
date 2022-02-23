<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineWhitelistRepository
{
    /**
     * Returns an already existing whitelist entry for the given ref-id
     * that is related to the given routine.
     *
     * @param int $routine_id
     * @param int $ref_id
     * @return IRoutineWhitelist|null
     */
    public function get(int $routine_id, int $ref_id) : ?IRoutineWhitelist;

    /**
     * Returns all existing white-list entries for the given routine-id.
     *
     * To retrieve routines as array-data true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool $array_data
     * @return IRoutineWhitelist[]
     */
    public function getAll(IRoutine $routine, bool $array_data = false) : array;

    /**
     * Creates or updates a white-list entry for the given ref- and routine-id.
     *
     * @param IRoutineWhitelist $entry
     * @return IRoutineWhitelist
     */
    public function add(IRoutineWhitelist $entry) : IRoutineWhitelist;

    /**
     * Removes an existing white-list entry.
     *
     * @param IRoutineWhitelist $entry
     * @return bool
     */
    public function remove(IRoutineWhitelist $entry) : bool;
}