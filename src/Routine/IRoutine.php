<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use DateTime;

/**
 * IRoutine describes the DAO of a routine.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutine
{
    // IRoutine attributes:
    public const F_CREATION_DATE = 'creation_date';
    public const F_ELONGATION    = 'elongation';
    public const F_HAS_OPT_OUT   = 'has_opt_out';
    public const F_IS_ACTIVE     = 'is_active';
    public const F_ORIGIN_TYPE   = 'origin_type';
    public const F_REF_ID        = 'ref_id';
    public const F_ROUTINE_TYPE  = 'routine_type';
    public const F_ROUTINE_ID    = 'routine_id';
    public const F_TITLE         = 'title';
    public const F_USER_ID       = 'usr_id';

    // IRoutine origin types:
    public const ORIGIN_TYPE_ADMINISTRATION = 1;
    public const ORIGIN_TYPE_REPOSITORY = 2;
    public const ORIGIN_TYPE_EXTERNAL = 3;
    public const ORIGIN_TYPE_UNKNOWN = 4;

    // IRoutine routine types:
    public const ROUTINE_TYPE_COURSE = 'crs';
    public const ROUTINE_TYPE_GROUP  = 'grp';

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutine
     */
    public function setRoutineId(int $routine_id) : IRoutine;

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
     * @return int
     */
    public function getOwnerId() : int;

    /**
     * @param int $owner_id
     * @return IRoutine
     */
    public function setOwnerId(int $owner_id) : IRoutine;

    /**
     * @return int
     */
    public function getOrigin() : int;

    /**
     * @param int $origin
     * @return IRoutine
     */
    public function setOrigin(int $origin) : IRoutine;

    /**
     * @return string
     */
    public function getRoutineType() : string;

    /**
     * @param string $routine_type
     * @return IRoutine
     */
    public function setRoutineType(string $routine_type) : IRoutine;

    /**
     * @return string
     */
    public function getTitle() : string;

    /**
     * @param string $title
     * @return IRoutine
     */
    public function setTitle(string $title) : IRoutine;

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
    public function hasOptOut() : bool;

    /**
     * @param bool $has_opt_out
     * @return IRoutine
     */
    public function setOptOut(bool $has_opt_out) : IRoutine;

    /**
     * @return int|null
     */
    public function getElongation() : ?int;

    /**
     * @param int|null $amount
     * @return IRoutine
     */
    public function setElongation(?int $amount) : IRoutine;

    /**
     * @return DateTime
     */
    public function getCreationDate() : DateTime;

    /**
     * @param DateTime $creation_date
     * @return IRoutine
     */
    public function setCreationDate(DateTime $creation_date) : IRoutine;

    /**
     * @return IRule[]
     */
    public function getRules() : array;

    /**
     * @param IRule[] $rules
     * @return IRoutine
     */
    public function setRules(array $rules) : IRoutine;

    /**
     * @return INotification[]
     */
    public function getNotifications() : array;

    /**
     * @param INotification[] $notifications
     * @return IRoutine
     */
    public function setNotifications(array $notifications) : IRoutine;
}