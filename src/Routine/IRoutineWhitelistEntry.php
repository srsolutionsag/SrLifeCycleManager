<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineWhitelistEntry
{
    /**
     * IRoutineWhitelistEntry attribute names
     */
    public const F_ID               = 'id';
    public const F_WHITELIST_TYPE   = 'whitelist_type';
    public const F_ROUTINE_ID       = 'routine_id';
    public const F_REF_ID           = 'ref_id';
    public const F_ACTIVE_UNTIL     = 'active_until';

    /**
     * IRoutineWhitelistEntry elongation type
     */
    public const WHITELIST_TYPE_OPT_OUT    = 1;
    public const WHITELIST_TYPE_ELONGATION = 2;

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
    public function getId() : ?int;

    /**
     * @param int $id
     * @return IRoutineWhitelistEntry
     */
    public function setId(int $id) : IRoutineWhitelistEntry;

    /**
     * @return int
     */
    public function getWhitelistType() : int;

    /**
     * @param int $type
     * @return IRoutineWhitelistEntry
     */
    public function setWhitelistType(int $type) : IRoutineWhitelistEntry;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineWhitelistEntry
     */
    public function setRoutineId(int $routine_id) : IRoutineWhitelistEntry;

    /**
     * @return int|null
     */
    public function getRefId() : ?int;

    /**
     * @param int $ref_id
     * @return IRoutineWhitelistEntry
     */
    public function setRefId(int $ref_id) : IRoutineWhitelistEntry;

    /**
     * @return DateTime|null
     */
    public function getActiveUntil() : ?DateTime;

    /**
     * @param DateTime $date
     * @return IRoutineWhitelistEntry
     */
    public function setActiveUntil(DateTime $date) : IRoutineWhitelistEntry;
}