<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;
use srag\Plugins\SrLifeCycleManager\Repository\ObjectHelper;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;

/**
 * This repository is responsible for all rule CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRuleRepository implements IRuleRepository
{
    use ObjectHelper;
    use DTOHelper;

    /**
     * @param ilDBInterface $database
     * @param ilTree        $tree
     */
    public function __construct(protected \ilDBInterface $database, ilTree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function get(int $rule_id): ?IRule
    {
        $query = "
            SELECT rule.rule_id, rule.lhs_type, rule.lhs_value, rule.rhs_type, rule.rhs_value, rule.operator, rel.routine_id 
                FROM srlcm_rule AS rule
                JOIN srlcm_routine_rule AS rel ON rel.rule_id = rule.rule_id
                WHERE rule.rule_id = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [$rule_id]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false): array
    {
        $query = "
            SELECT rule.rule_id, rule.lhs_type, rule.lhs_value, rule.rhs_type, rule.rhs_value, rule.operator, rel.routine_id 
                FROM srlcm_rule AS rule
                JOIN srlcm_routine_rule AS rel ON rel.rule_id = rule.rule_id
                WHERE rel.routine_id = %s
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $routine->getRoutineId() ?? 0,
                    ]
                )
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRefIdAndRoutineType(int $ref_id, string $routine_type): array
    {
        $query = "
            SELECT rule.rule_id, rule.lhs_type, rule.lhs_value, rule.rhs_type, rule.rhs_value, rule.operator, relation.routine_id 
                FROM  srlcm_rule AS rule
                JOIN srlcm_routine_rule AS relation ON relation.rule_id = rule.rule_id
                JOIN srlcm_routine AS routine ON routine.routine_id = relation.routine_id
                JOIN srlcm_assigned_routine AS assignment ON assignment.routine_id = routine.routine_id
                WHERE routine.routine_type = %s
                AND assignment.is_active = 1
                AND (
                    (assignment.is_recursive = 1 AND assignment.ref_id IN ({$this->getParentIdsForSqlComparison($ref_id)})) OR
                    (assignment.is_recursive = 0 AND assignment.ref_id IN (%s, %s))
                )
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['text', 'integer', 'integer'],
                    [
                        $routine_type,
                        $this->getParentId($ref_id),
                        $ref_id,
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRule $rule): IRule
    {
        if (null === $rule->getRuleId()) {
            return $this->insertRule($rule);
        }

        return $this->updateRule($rule);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRule $rule): bool
    {
        if (null === $rule->getRuleId()) {
            return true;
        }

        $query = "
            DELETE rule, relation
                FROM (SELECT %s AS rule_id) AS deletable
                LEFT OUTER JOIN srlcm_rule AS rule ON rule.rule_id = deletable.rule_id
                LEFT OUTER JOIN srlcm_routine_rule AS relation ON relation.rule_id = deletable.rule_id
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer'],
            [$rule->getRuleId()]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function empty(IRoutine $routine): IRule
    {
        if (null === $routine->getRoutineId()) {
            throw new LogicException("Cannot relate rule to routine that has not been stored yet.");
        }

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
    protected function updateRule(IRule $rule): IRule
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
    protected function insertRule(IRule $rule): IRule
    {
        $query = "
            INSERT INTO srlcm_rule (rule_id, lhs_type, lhs_value, rhs_type, rhs_value, operator)
                VALUES (%s, %s, %s, %s, %s, %s)
            ;
        ";

        $rule_id = (int) $this->database->nextId('srlcm_rule');
        $this->database->manipulateF(
            $query,
            ['integer', 'text', 'text', 'text', 'text', 'text'],
            [
                $rule_id,
                $rule->getLhsType(),
                $rule->getLhsValue(),
                $rule->getRhsType(),
                $rule->getRhsValue(),
                $rule->getOperator(),
            ]
        );

        $query = "INSERT INTO srlcm_routine_rule (routine_id, rule_id) VALUES (%s, %s);";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer'],
            [
                $rule->getRoutineId(),
                $rule_id,
            ]
        );

        return $rule->setRuleId($rule_id);
    }

    /**
     * @param array $query_result
     * @return IRule
     */
    protected function transformToDTO(array $query_result): IRule
    {
        return new Rule(
            $query_result[IRule::F_LHS_TYPE],
            $query_result[IRule::F_LHS_VALUE],
            $query_result[IRule::F_OPERATOR],
            $query_result[IRule::F_RHS_TYPE],
            $query_result[IRule::F_RHS_VALUE],
            (int) $query_result[IRule::F_ROUTINE_ID],
            (int) $query_result[IRule::F_RULE_ID]
        );
    }
}
