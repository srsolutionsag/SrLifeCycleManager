<?php

namespace srag\Plugins\SrLifeCycleManager\Assignment;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignment
{
    // IAssignedRoutine attributes:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_IS_ACTIVE = 'is_active';
    public const F_RECURSIVE = 'is_recursive';
    public const F_REF_ID = 'ref_id';

    /**
     * @return IRoutine
     */
    public function getRoutine() : IRoutine;

    /**
     * @param IRoutine $routine
     * @return IRoutineAssignment
     */
    public function setRoutine(IRoutine $routine) : IRoutineAssignment;

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

    /**
     * @return int|null
     */
    public function getRefId() : ?int;

    /**
     * @param int $ref_id
     * @return IRoutineAssignment
     */
    public function setRefId(int $ref_id) : IRoutineAssignment;
}