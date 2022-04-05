<?php

namespace srag\Plugins\SrLifeCycleManager\Assignment;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignmentRepository
{
    /**
     * Fetches an existing assignment from the database for the given
     * routine and object (ref-id).
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @return IRoutineAssignment|null
     */
    public function get(IRoutine $routine, int $ref_id) : ?IRoutineAssignment;

    /**
     * Fetches all existing assignments of a given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param IRoutine $routine
     * @param bool $array_data
     * @return IRoutineAssignment[]
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array;

    /**
     * Fetches all existing assignments of a given object (ref-id).
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int $ref_id
     * @param bool $array_data
     * @return IRoutineAssignment[]
     */
    public function getByRefId(int $ref_id, bool $array_data = false) : array;

    /**
     * Creates or updates the given routine assignment in the database.
     *
     * @param IRoutineAssignment $assignment
     * @return IRoutineAssignment
     */
    public function store(IRoutineAssignment $assignment) : IRoutineAssignment;

    /**
     * @param IRoutine $routine
     * @return IRoutineAssignment
     */
    public function empty(IRoutine $routine) : IRoutineAssignment;
}