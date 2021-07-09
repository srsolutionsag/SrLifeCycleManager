<?php

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Class ilSrRuleRepository
 */
final class ilSrRuleRepository implements IRuleRepository
{
    /**
     * @inheritDoc
     */
    public function get(int $id) : ?Rule
    {
        /**
         * @var $ar_rule ilSrRule
         */
        $ar_rule = ilSrRule::find($id);
        return (null !== $ar_rule) ?
            $this->transformToDTO($ar_rule) : null
        ;
    }

    /**
     * @inheritDoc
     */
    public function store(IRule $rule) : Rule
    {
        // fetch existing rule or create new AR instance
        if (null !== $rule->getId()) {
            $ar_rule = ilSrRule::find($rule->getId()) ?? new ilSrRule();
        } else {
            $ar_rule = new ilSrRule();
        }

        $ar_rule
            ->setLhsType($rule->getLhsType())
            ->setLhsValue($rule->getLhsValue())
            ->setOperator($rule->getOperator())
            ->setRhsType($rule->getRhsType())
            ->setRhsValue($rule->getRhsValue())
            ->store();
        ;

        return $this->transformToDTO($ar_rule);
    }

    /**
     * @inheritDoc
     */
    public function getAllAsDTO() : ?array
    {
        /**
         * @var $ar_rules ilSrRule[]
         */
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return null;

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToDTO($ar_rule);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getAllAsArray() : array
    {
        /**
         * @var $ar_rules ilSrRule[]
         */
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return [];

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToArray($ar_rule);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getAllForValueTypes(array $value_types) : ?array
    {
        global $DIC;

        $query_types = [];
        $query_values = [];
        $query = "SELECT * FROM " . ilSrRule::TABLE_NAME;

        for ($i = 0, $type_count = count($value_types); $i < $type_count; $i++) {
            if ($i === 0) {
                $query .= " WHERE lhs_type = %s";
                $query .= " OR rhs_type = %s";
            } else {
                $query .= " OR lhs_type = %s";
                $query .= " OR rhs_type = %s";
            }

            // add query-type and query-value twice
            $query_types[] = 'text';
            $query_types[] = 'text';
            $query_values[] = $value_types[$i];
            $query_values[] = $value_types[$i];
        }

        $results = $DIC->database()->fetchAll(
            $DIC->database()->queryF($query, $query_types, $query_values)
        );

        if (empty($results)) return null;

        $rules = [];
        foreach ($results as $rule_data) {
            $rules[] = $this->transformToDTO($rule_data);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function delete(IRule $rule) : bool
    {
        // nothing to do, rule hasn't been saved to the
        // database yet
        if (null === $rule->getId()) return true;

        // abort if the given rule was not found in the
        // database.
        $ar_rule = ilSrRule::find($rule->getId());
        if (null === $ar_rule) return false;

        $ar_routines = ilSrRoutineRule::where([
            ilSrRoutineRule::F_RULE_ID => $rule->getId(),
        ])->get();

        // delete all routine relations of this rule.
        if (!empty($ar_routines)) {
            foreach ($ar_routines as $relation) {
                $relation->delete();
            }
        }

        // finally delete the rule itself and return.
        $ar_rule->delete();
        return false;
    }

    /**
     * @inheritDoc
     */
    public function transformToDTO(IRule $rule) : Rule
    {
        return new Rule(
            $rule->getId(),
            $rule->getLhsType(),
            $rule->getLhsValue(),
            $rule->getOperator(),
            $rule->getRhsType(),
            $rule->getRhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function transformToArray(IRule $rule) : array
    {
        return  [
            ilSrRule::F_ID => $rule->getId(),
            ilSrRule::F_LHS_TYPE => $rule->getLhsType(),
            ilSrRule::F_LHS_VALUE => $rule->getLhsValue(),
            ilSrRule::F_OPERATOR => $rule->getOperator(),
            ilSrRule::F_RHS_TYPE => $rule->getRhsType(),
            ilSrRule::F_RHS_VALUE => $rule->getRhsValue(),
        ];
    }
}