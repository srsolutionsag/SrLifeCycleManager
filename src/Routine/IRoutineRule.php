<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * Interface IRoutineNotification defines how a routine-rule relation must look like.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineRule
{
    /**
     * @return int|null
     */
    public function getId() : ?int;

    /**
     * @param int $id
     * @return IRoutineRule
     */
    public function setId(int $id) : IRoutineRule;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineRule
     */
    public function setRoutineId(int $routine_id) : IRoutineRule;

    /**
     * @return int|null
     */
    public function getRuleId() : ?int;

    /**
     * @param int $rule_id
     * @return IRoutineRule
     */
    public function setRuleId(int $rule_id) : IRoutineRule;
}