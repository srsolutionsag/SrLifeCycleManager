<?php

namespace srag\Plugins\_SrLifeCycleManager\Rule;

/**
 * IRoutineRuleRelation describes the relationship between
 * a routine and a rule.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineRuleRelation
{
    /**
     * IRoutineRule attribute names
     */
    public const F_RELATION_ID = 'relation_id';
    public const F_ROUTINE_ID = 'routine_id';
    public const F_RULE_ID = 'rule_id';

    /**
     * @return int|null
     */
    public function getRelationId() : ?int;

    /**
     * @param int $relation_id
     * @return IRoutineRuleRelation
     */
    public function setRelationId(int $relation_id) : IRoutineRuleRelation;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineRuleRelation
     */
    public function setRoutineId(int $routine_id) : IRoutineRuleRelation;

    /**
     * @return int|null
     */
    public function getRuleId() : ?int;

    /**
     * @param int $rule_id
     * @return IRoutineRuleRelation
     */
    public function setRuleId(int $rule_id) : IRoutineRuleRelation;
}