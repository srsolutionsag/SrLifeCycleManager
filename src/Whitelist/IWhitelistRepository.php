<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Whitelist;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IWhitelistRepository
{
    /**
     * Fetches an existing whitelist entry from the database for the
     * given routine and object (ref-id).
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return IWhitelistEntry|null
     */
    public function get(IRoutine $routine, int $ref_id): ?IWhitelistEntry;

    /**
     * Fetches all existing whitelist entries from the database which are
     * related to the given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool     $array_data
     * @return array
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false): array;

    /**
     * Creates or updates an existing whitelist entry in the database.
     *
     * @param IWhitelistEntry $entry
     * @return IWhitelistEntry
     */
    public function store(IWhitelistEntry $entry): IWhitelistEntry;

    /**
     * Deletes all whitelist entries for the given ref-id. This is usually
     * the case if an ilObject was deleted.
     *
     * @param int $ref_id
     * @return bool
     */
    public function delete(int $ref_id): bool;

    /**
     * Returns an empty instance of a whitelist entry for the given information.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @param int      $user_id
     * @return IWhitelistEntry
     */
    public function empty(IRoutine $routine, int $ref_id, int $user_id): IWhitelistEntry;
}
