<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineWhitelist
{
    public const F_WHITELIST_ID = 'whitelist_id';
    public const F_WHITELIST_TYPE = 'whitelist_type';
    public const F_ROUTINE_ID = 'routine_id';
    public const F_REF_ID = 'ref_id';
    public const F_ACTIVE_UNTIL = 'active_until';

    public const WHITELIST_TYPE_ELONGATION = 2;
    public const WHITELIST_TYPE_OPT_OUT    = 1;

    /**
     * @var int[] whitelist types
     */
    public const WHITELIST_TYPES = [
        self::WHITELIST_TYPE_OPT_OUT,
        self::WHITELIST_TYPE_ELONGATION,
    ];

    /**
     * @return int|null
     */
    public function getWhitelistId() : ?int;

    /**
     * @param int $whitelist_id
     * @return IRoutineWhitelist
     */
    public function setWhitelistId(int $whitelist_id) : IRoutineWhitelist;

    /**
     * @return int
     */
    public function getWhitelistType() : int;

    /**
     * @param int $type
     * @return IRoutineWhitelist
     */
    public function setWhitelistType(int $type) : IRoutineWhitelist;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineWhitelist
     */
    public function setRoutineId(int $routine_id) : IRoutineWhitelist;

    /**
     * @return int|null
     */
    public function getRefId() : ?int;

    /**
     * @param int $ref_id
     * @return IRoutineWhitelist
     */
    public function setRefId(int $ref_id) : IRoutineWhitelist;

    /**
     * @return DateTime|null
     */
    public function getActiveUntil() : ?DateTime;

    /**
     * @param DateTime|null $date
     * @return IRoutineWhitelist
     */
    public function setActiveUntil(?DateTime $date) : IRoutineWhitelist;
}