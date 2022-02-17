<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * IRoutineAwareRule describes the DTO of a rule.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Other than the rule DAO, the rule DTO should also contain
 * methods that regard the relationship to a routine.
 *
 * This way CRUD operations will be easier because only an instance of
 * this interface must be expected by the repository in order to manage
 * a rules relations too.
 */
interface IRoutineAwareRule extends IRule
{
    /**
     * @return int
     */
    public function getRoutineId() : int;

    /**
     * @param int $routine_id
     * @return IRoutineAwareRule
     */
    public function setRoutineId(int $routine_id) : IRoutineAwareRule;
}