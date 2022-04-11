<?php

namespace srag\Plugins\SrLifeCycleManager\Assignment;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignmentRepository
{
    /**
     * Fetches an existing assignment from the database for the given
     * routine and object (ref-id).
     *
     * @param int $routine_id
     * @param int $ref_id
     * @return IRoutineAssignment|null
     */
    public function get(int $routine_id, int $ref_id) : ?IRoutineAssignment;

    /**
     * Fetches all existing assignments of a given routine.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $routine_id
     * @param bool $array_data
     * @return IRoutineAssignment[]
     */
    public function getByRoutineId(int $routine_id, bool $array_data = false) : array;

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
     * Deletes an existing routine assignment from the database.
     *
     * @param IRoutineAssignment $assignment
     * @return bool
     */
    public function delete(IRoutineAssignment $assignment) : bool;

    /**
     * Returns an empty instance of the an assignment.
     *
     * @return IRoutineAssignment
     */
    public function empty() : IRoutineAssignment;
}