<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * Interface IRoutine defines how a routine must look like.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @TODO: remove elongation_possible attribute
 */
interface IRoutine
{
    public const ORIGIN_TYPE_ADMINISTRATION = 1;
    public const ORIGIN_TYPE_REPOSITORY = 2;
    public const ORIGIN_TYPE_EXTERNAL = 3;

    /**
     * @var int[] origin types (where routines were created from).
     */
    public const ORIGIN_TYPES = [
        self::ORIGIN_TYPE_ADMINISTRATION,
        self::ORIGIN_TYPE_REPOSITORY,
        self::ORIGIN_TYPE_EXTERNAL,
    ];

    /**
     * @var string[] origin types mapped to their translation.
     */
    public const ORIGIN_TYPE_NAMES = [
        self::ORIGIN_TYPE_ADMINISTRATION => 'routine_origin_type_admin',
        self::ORIGIN_TYPE_REPOSITORY     => 'routine_origin_type_repo',
        self::ORIGIN_TYPE_EXTERNAL       => 'routine_origin_type_ext',
    ];

    /**
     * @return int|null
     */
    public function getId() : ?int;

    /**
     * @param int $id
     * @return IRoutine
     */
    public function setId(int $id) : IRoutine;

    /**
     * @return int
     */
    public function getRefId() : int;

    /**
     * @param int $ref_id
     * @return IRoutine
     */
    public function setRefId(int $ref_id) : IRoutine;

    /**
     * @return bool
     */
    public function isActive() : bool;

    /**
     * @param bool $is_active
     * @return IRoutine
     */
    public function setActive(bool $is_active) : IRoutine;

    /**
     * @return bool
     */
    public function isElongationPossible() : bool;

    /**
     * @param bool $is_possible
     * @return IRoutine
     */
    public function setElongationPossible(bool $is_possible) : IRoutine;

    /**
     * @return int|null
     */
    public function getElongationDays() : ?int;

    /**
     * @param int $days
     * @return IRoutine
     */
    public function setElongationDays(int $days) : IRoutine;

    /**
     * @return bool
     */
    public function isOptOutPossible() : bool;

    /**
     * @param bool $is_possible
     * @return IRoutine
     */
    public function setOptOutPossible(bool $is_possible) : IRoutine;

    /**
     * @return int
     */
    public function getOriginType() : int;

    /**
     * @param int $type
     * @return IRoutine
     */
    public function setOriginType(int $type) : IRoutine;

    /**
     * @return int
     */
    public function getOwnerId() : int;

    /**
     * @param int $owner_id
     * @return IRoutine
     */
    public function setOwnerId(int $owner_id) : IRoutine;

    /**
     * @return \DateTime
     */
    public function getCreationDate() : \DateTime;

    /**
     * @param \DateTime $date
     * @return IRoutine
     */
    public function setCreationDate(\DateTime $date) : IRoutine;
}