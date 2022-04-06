<?php

namespace srag\Plugins\SrLifeCycleManager\Assignment;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignment extends IRoutineAssignmentIntention
{
    // IAssignedRoutine attributes:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_IS_ACTIVE = 'is_active';
    public const F_RECURSIVE = 'is_recursive';
    public const F_REF_ID = 'ref_id';

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