<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Assignment;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignment
{
    // IAssignedRoutine attributes:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_REF_ID = 'ref_id';
    public const F_USER_ID = 'usr_id';
    public const F_IS_ACTIVE = 'is_active';
    public const F_RECURSIVE = 'is_recursive';

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int|null $routine_id
     * @return IRoutineAssignment
     */
    public function setRoutineId(?int $routine_id) : IRoutineAssignment;

    /**
     * @return int|null
     */
    public function getRefId() : ?int;

    /**
     * @param int|null $ref_id
     * @return IRoutineAssignment
     */
    public function setRefId(?int $ref_id) : IRoutineAssignment;

    /**
     * @return int
     */
    public function getUserId() : int;

    /**
     * @param int $user_id
     * @return IRoutineAssignment
     */
    public function setUserId(int $user_id) : IRoutineAssignment;

    /**
     * @return bool
     */
    public function isActive() : bool;

    /**
     * @param bool $is_active
     * @return IRoutineAssignment
     */
    public function setActive(bool $is_active) : IRoutineAssignment;

    /**
     * @return bool
     */
    public function isRecursive() : bool;

    /**
     * @param bool $is_recursive
     * @return IRoutineAssignment
     */
    public function setRecursive(bool $is_recursive) : IRoutineAssignment;
}