<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTimeImmutable;

/**
 * IRoutine describes the DAO of a routine.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutine
{
    // IRoutine attributes:
    public const F_CREATION_DATE = 'creation_date';
    public const F_ELONGATION = 'elongation';
    public const F_COOLDOWN = 'elongation_cooldown';
    public const F_HAS_OPT_OUT = 'has_opt_out';
    public const F_ORIGIN_TYPE = 'origin_type';
    public const F_ROUTINE_TYPE = 'routine_type';
    public const F_ROUTINE_ID = 'routine_id';
    public const F_TITLE = 'title';
    public const F_USER_ID = 'usr_id';

    // IRoutine origin types:
    public const ORIGIN_TYPE_ADMINISTRATION = 1;
    public const ORIGIN_TYPE_REPOSITORY = 2;
    public const ORIGIN_TYPE_EXTERNAL = 3;
    public const ORIGIN_TYPE_UNKNOWN = 4;

    // IRoutine routine types:
    public const ROUTINE_TYPE_COURSE = 'crs';
    public const ROUTINE_TYPE_SURVEY = 'svy';
    public const ROUTINE_TYPE_GROUP = 'grp';

    /**
     * @var string[] list of supported routine-types.
     */
    public const ROUTINE_TYPES = [
        self::ROUTINE_TYPE_COURSE,
        self::ROUTINE_TYPE_SURVEY,
        self::ROUTINE_TYPE_GROUP,
    ];

    /**
     * @return int|null
     */
    public function getRoutineId(): ?int;

    /**
     * @param int $routine_id
     * @return IRoutine
     */
    public function setRoutineId(int $routine_id): IRoutine;

    /**
     * @return int
     */
    public function getOwnerId(): int;

    /**
     * @param int $owner_id
     * @return IRoutine
     */
    public function setOwnerId(int $owner_id): IRoutine;

    /**
     * @return int
     */
    public function getOrigin(): int;

    /**
     * @param int $origin
     * @return IRoutine
     */
    public function setOrigin(int $origin): IRoutine;

    /**
     * @return string
     */
    public function getRoutineType(): string;

    /**
     * @param string $routine_type
     * @return IRoutine
     */
    public function setRoutineType(string $routine_type): IRoutine;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return IRoutine
     */
    public function setTitle(string $title): IRoutine;

    /**
     * @return bool
     */
    public function hasOptOut(): bool;

    /**
     * @param bool $has_opt_out
     * @return IRoutine
     */
    public function setOptOut(bool $has_opt_out): IRoutine;

    /**
     * @return int|null
     */
    public function getElongation(): ?int;

    /**
     * @param int|null $amount
     * @return IRoutine
     */
    public function setElongation(?int $amount): IRoutine;

    /**
     * @return int|null
     */
    public function getElongationCooldown(): ?int;

    /**
     * @param int|null $amount_in_days
     * @return IRoutine
     */
    public function setElongationCooldown(?int $amount_in_days): IRoutine;

    /**
     * @return DateTimeImmutable
     */
    public function getCreationDate(): DateTimeImmutable;

    /**
     * @param DateTimeImmutable $creation_date
     * @return IRoutine
     */
    public function setCreationDate(DateTimeImmutable $creation_date): IRoutine;
}
