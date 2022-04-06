<?php

namespace srag\Plugins\SrLifeCycleManager\Assignment;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineAssignmentIntention
{
    // IRoutineAssignmentIntention intentions:
    public const UNKNOWN_ASSIGNMENT = -1;
    public const ROUTINE_ASSIGNMENT = 0;
    public const OBJECT_ASSIGNMENT = 1;
    public const EDIT_ASSIGNMENT = 2;

    /**
     * Returns the intention of the
     *
     * @return int
     */
    public function getIntention() : int;
}