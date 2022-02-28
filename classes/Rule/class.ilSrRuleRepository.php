<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRuleRepository implements IRuleRepository
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(int $rule_id) : ?IRule
    {
        $query = "
            SELECT rule.lhs_type, rule.lhs_value, rule.rhs_type, rule.rhs_value, rule.operator, rel.routine_id 
                FROM srlcm_rule AS rule
                JOIN srlcm_routine_rule AS rel ON rel.rule_id = rule.rule_id
                WHERE rule.rule_id = %s
            ;
        ";

        $result = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [$rule_id]
            )
        );

        if (!empty($result)) {
            $this->transformToDTO($result[0]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array
    {
        $query = "
            SELECT rule.lhs_type, rule.lhs_value, rule.rhs_type, rule.rhs_value, rule.operator, rel.routine_id 
                FROM srlcm_rule AS rule
                JOIN srlcm_routine_rule AS rel ON rel.rule_id = rule.rule_id
                WHERE rel.routine_id = %s
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [$routine->getRoutineId()]
            )
        );

        if ($array_data) {
            return $results;
        }

        $rules = [];
        foreach ($results as $result) {
            $rules[] = $this->transformToDTO($result);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function store(IRule $rule) : IRule
    {
        if (null === $rule->getRuleId()) {
            return $this->insertRule($rule);
        }

        return $this->updateRule($rule);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRule $rule) : bool
    {
        if (null === $rule->getRuleId()) {
            return true;
        }

        $query = "
            DELETE FROM rule, rel
                FROM srlcm_rule AS rule
                JOIN srlcm_routine_rule AS rel ON rule.rule_id = rel.rule_id
                WHERE rule.rule_id = %s
            ;
        ";

        $result = $this->database->manipulateF(
            $query,
            ['integer'],
            [$rule->getRuleId()]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function empty(IRoutine $routine) : IRule
    {
        return new Rule(
            '',
            '',
            '',
            '',
            '',
            $routine->getRoutineId()
        );
    }

    /**
     * @param IRule $rule
     * @return IRule
     */
    protected function updateRule(IRule $rule) : IRule
    {
        $query = "
            UPDATE srlcm_rule SET
                lhs_type = %s, 
                lhs_value = %s, 
                rhs_type = %s,
                rhs_value = %s, 
                operator = %s
                WHERE rule_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['text', 'text', 'text', 'text', 'text', 'integer'],
            [
                $rule->getLhsType(),
                $rule->getLhsValue(),
                $rule->getRhsType(),
                $rule->getRhsValue(),
                $rule->getOperator(),
                $rule->getRuleId(),
            ]
        );

        return $rule;
    }

    /**
     * @param IRule $rule
     * @return IRule
     */
    protected function insertRule(IRule $rule) : IRule
    {
        $query = "
            INSERT INTO srlcm_rule (lhs_type, lhs_value, rhs_type, rhs_value, operator)
                VALUES (%s, %s, %s, %s, %s)
            ;
        ";

        $rule_id = $this->database->manipulateF(
            $query,
            ['text', 'text', 'text', 'text', 'text'],
            [
                $rule->getLhsType(),
                $rule->getLhsValue(),
                $rule->getRhsType(),
                $rule->getRhsValue(),
                $rule->getOperator(),
            ]
        );

        $query = "
            INSERT INTO srlcm_routine_rule (routine_id, rule_id)
                VALUES (%s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer'],
            [
                $rule->getRoutineId(),
                $rule->getRuleId(),
            ]
        );

        return $rule->setRuleId((int) $rule_id);
    }

    /**
     * @param array $query_result
     * @return IRule
     */
    protected function transformToDTO(array $query_result) : IRule
    {
        return new Rule(
            $query_result[IRule::F_LHS_TYPE],
            $query_result[IRule::F_LHS_VALUE],
            $query_result[IRule::F_OPERATOR],
            $query_result[IRule::F_RHS_TYPE],
            $query_result[IRule::F_RHS_VALUE],
            $query_result[IRule::F_ROUTINE_ID]
        );
    }
}