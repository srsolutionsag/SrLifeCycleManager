<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IWhitelistRepository
{
    /**
     * Extends the given object (ref-id) from the given Routine by the
     * possible elongation by creating a whitelist entry.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return bool
     */
    public function extendObjectByRefId(IRoutine $routine, int $ref_id) : bool;

    /**
     * Returns if the given object (ref-id) is already extended.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return bool
     */
    public function isObjectExtended(IRoutine $routine, int $ref_id) : bool;

    /**
     * Extends the given object (ref-id) from the given Routine forever
     * by creating a whitelist entry.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return bool
     */
    public function optOutObjectByRefId(IRoutine $routine, int $ref_id) : bool;

    /**
     * Returns if the given object (ref-id) is already opted-out.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return bool
     */
    public function isObjectOptedOut(IRoutine $routine, int $ref_id) : bool;
}