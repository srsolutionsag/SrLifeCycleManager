<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IWhitelistEntry
{
    // IWhitelistEntry attribute names:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_REF_ID = 'ref_id';
    public const F_IS_OPT_OUT = 'is_opt_out';
    public const F_ELONGATION = 'elongation';
    public const F_DATE = 'date';

    /**
     * @return int
     */
    public function getRoutineId() : int;

    /**
     * @param int $routine_id
     * @return IWhitelistEntry
     */
    public function setRoutineId(int $routine_id) : IWhitelistEntry;

    /**
     * @return int
     */
    public function getRefId() : int;

    /**
     * @param int $ref_id
     * @return IWhitelistEntry
     */
    public function setRefId(int $ref_id) : IWhitelistEntry;

    /**
     * @return bool
     */
    public function isOptOut() : bool;

    /**
     * @param bool $is_opt_out
     * @return IWhitelistEntry
     */
    public function setOptOut(bool $is_opt_out) : IWhitelistEntry;

    /**
     * @return int|null
     */
    public function getElongation() : ?int;

    /**
     * @param int $elongation
     * @return IWhitelistEntry
     */
    public function setElongation(int $elongation) : IWhitelistEntry;

    /**
     * @return DateTime
     */
    public function getDate() : DateTime;

    /**
     * @param DateTime $date
     * @return IWhitelistEntry
     */
    public function setDate(DateTime $date) : IWhitelistEntry;
}