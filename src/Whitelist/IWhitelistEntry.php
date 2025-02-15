<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Whitelist;

use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IWhitelistEntry
{
    // IWhitelistEntry attribute names:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_REF_ID = 'ref_id';
    public const F_USER_ID = 'usr_id';
    public const F_IS_OPT_OUT = 'is_opt_out';
    public const F_EXPIRY_DATE = 'expiry_date';
    public const F_DATE = 'date';

    /**
     * @return int
     */
    public function getRoutineId(): int;

    /**
     * @param int $routine_id
     * @return IWhitelistEntry
     */
    public function setRoutineId(int $routine_id): IWhitelistEntry;

    /**
     * @return int
     */
    public function getRefId(): int;

    /**
     * @param int $ref_id
     * @return IWhitelistEntry
     */
    public function setRefId(int $ref_id): IWhitelistEntry;

    /**
     * @return int
     */
    public function getUserId(): int;

    /**
     * @param int $user_id
     * @return IWhitelistEntry
     */
    public function setUserId(int $user_id): IWhitelistEntry;

    /**
     * @return bool
     */
    public function isOptOut(): bool;

    /**
     * @param bool $is_opt_out
     * @return IWhitelistEntry
     */
    public function setOptOut(bool $is_opt_out): IWhitelistEntry;

    /**
     * @return DateTimeImmutable|null
     */
    public function getExpiryDate(): ?DateTimeImmutable;

    /**
     * @param DateTimeImmutable $date
     * @return IWhitelistEntry
     */
    public function setExpiryDate(DateTimeImmutable $date): IWhitelistEntry;

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): ?DateTimeImmutable;

    /**
     * @param DateTimeImmutable $date
     * @return IWhitelistEntry
     */
    public function setDate(DateTimeImmutable $date): IWhitelistEntry;
}
