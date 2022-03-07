<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

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
    public function get(IRoutine $routine, int $ref_id) : ?IWhitelistEntry;

    /**
     * Creates or updates an existing whitelist entry in the database.
     *
     * @param IWhitelistEntry $entry
     * @return IWhitelistEntry
     */
    public function store(IWhitelistEntry $entry) : IWhitelistEntry;

    /**
     * Deletes all whitelist entries for the given ref-id. This is usually
     * the case if an ilObject was deleted.
     *
     * @param int $ref_id
     * @return bool
     */
    public function delete(int $ref_id) : bool;
}