<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineAwareRule;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineRuleRelation;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Class ilSrRuleRepository
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
    public function get(int $routine_id, int $rule_id) : ?IRoutineAwareRule
    {
        /** @var $ar_rule ilSrRule|null */
        $ar_rule = ilSrRule::find($rule_id);
        if (null === $ar_rule) {
            return null;
        }

        /** @var $ar_relation ilSrRoutineRule */
        $ar_relation = $this->getRelationList($routine_id, $rule_id)->first();

        return $this->transformToDTO($ar_rule, $ar_relation);
    }

    /**
     * @inheritDoc
     */
    public function getAll(int $routine_id, bool $array_data = false) : array
    {
        $routine_table = ilSrRoutine::TABLE_NAME;
        $relation_table = ilSrRoutineRule::TABLE_NAME;
        $rule_table = ilSrRule::TABLE_NAME;

        $query = "
            SELECT rel.routine_id, rel.rule_id, rule.lhs_type, rule.lhs_value, rule.operator, rule.rhs_type, rule.rhs_value 
                FROM $routine_table AS routine
                JOIN $relation_table AS rel ON routine.routine_id = rel.routine_id
                JOIN $rule_table AS rule ON rel.rule_id = rule.rule_id
                WHERE routine.routine_id = $routine_id
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        if ($array_data) {
            return $results;
        }

        $rules = [];
        foreach ($results as $result) {
            $rules[] = new Rule(
                $result[IRule::F_LHS_TYPE],
                $result[IRule::F_LHS_VALUE],
                $result[IRule::F_OPERATOR],
                $result[IRule::F_RHS_TYPE],
                $result[IRule::F_RHS_VALUE],
                $result[IRoutineRuleRelation::F_ROUTINE_ID],
                $result[IRoutineRuleRelation::F_RULE_ID]
            );
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getEmpty(int $routine_id) : ?IRoutineAwareRule
    {
        return new Rule(
            "",
            null,
            "",
            "",
            null,
            $routine_id
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutineAwareRule $rule) : IRoutineAwareRule
    {
        $ar_rule = (null !== $rule->getRuleId()) ?
            (ilSrRule::find($rule->getRuleId()) ?? new ilSrRule()) :
            new ilSrRule()
        ;

        $ar_rule
            ->setLhsType($rule->getLhsType())
            ->setLhsValue($rule->getLhsValue())
            ->setOperator($rule->getOperator())
            ->setRhsType($rule->getRhsType())
            ->setRhsValue($rule->getRhsValue())
            ->store()
        ;

        $ar_relations = $this->getRelationList(
            $rule->getRoutineId(),
            $ar_rule->getRuleId()
        );

        /** @var $ar_relation ilSrRoutineRule */
        if (empty($ar_relations->get())) {
            $ar_relation = new ilSrRoutineRule();
            $ar_relation
                ->setRoutineId($rule->getRoutineId())
                ->setRuleId($ar_rule->getRuleId())
                ->store()
            ;
        } else {
            $ar_relation = $ar_relations->first();
        }

        return $this->transformToDTO($ar_rule, $ar_relation);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutineAwareRule $rule) : bool
    {
        // nothing to do, rule hasn't been saved to the database yet.
        if (null === $rule->getRuleId()) {
            return true;
        }

        $ar_rule = ilSrRule::find($rule->getRuleId());
        if (null !== $ar_rule) {
            $ar_relations = $this->getRelationList(
                $rule->getRoutineId(),
                $rule->getRuleId()
            );

            foreach ($ar_relations->get() as $ar_relation) {
                $ar_relation->delete();
            }

            $ar_rule->delete();
            return true;
        }

        return false;
    }

    /**
     * Helper function that returns the ar list of the routine-rule relations.
     *
     * @param int $routine_id
     * @param int $rule_id
     * @return ActiveRecordList
     */
    protected function getRelationList(int $routine_id, int $rule_id) : ActiveRecordList
    {
        return ilSrRoutineRule::where([
            IRoutineRuleRelation::F_ROUTINE_ID => $routine_id,
            IRoutineRuleRelation::F_RULE_ID => $rule_id,
        ], '=');
    }

    /**
     * Helper function that transforms the given rule and relation data
     * to a rule DTO.
     *
     * @param IRule                $ar_rule
     * @param IRoutineRuleRelation $ar_relation
     * @return IRoutineAwareRule
     */
    protected function transformToDTO(IRule $ar_rule, IRoutineRuleRelation $ar_relation) : IRoutineAwareRule
    {
        return new Rule(
            $ar_rule->getLhsType(),
            $ar_rule->getLhsValue(),
            $ar_rule->getOperator(),
            $ar_rule->getRhsType(),
            $ar_rule->getRhsValue(),
            $ar_relation->getRoutineId(),
            $ar_rule->getRuleId()
        );
    }

    /**
     * Helper function that transforms the given rule and relation data
     * to array-data.
     *
     * @param IRule                $ar_rule
     * @param IRoutineRuleRelation $ar_relation
     * @return array<string, mixed>
     */
    protected function transformToArray(IRule $ar_rule, IRoutineRuleRelation $ar_relation) : array
    {
        return  [
            IRoutineRuleRelation::F_ROUTINE_ID => $ar_relation->getRoutineId(),
            IRule::F_RULE_ID => $ar_rule->getRuleId(),
            IRule::F_LHS_TYPE => $ar_rule->getLhsType(),
            IRule::F_LHS_VALUE => $ar_rule->getLhsValue(),
            IRule::F_OPERATOR => $ar_rule->getOperator(),
            IRule::F_RHS_TYPE => $ar_rule->getRhsType(),
            IRule::F_RHS_VALUE => $ar_rule->getRhsValue(),
        ];
    }
}