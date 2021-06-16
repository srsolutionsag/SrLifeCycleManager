<?php

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
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
        $ar_rule = ilSrRule::find($id);
        return (null !== $ar_rule) ?
            $this->transformToEntity($ar_rule) : null
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

        return $this->transformToEntity($ar_rule);
    }

    /**
     * @inheritDoc
     */
    public function getAllAsDTO() : ?array
    {
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return null;

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToEntity($ar_rule);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getAllAsArray() : array
    {
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return [];

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToArrayData($ar_rule);
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
            $DIC->database()->queryF($query, $query_types, $query_values, \ilDBConstants::FETCHMODE_ASSOC)
        );

        if (empty($results)) return null;

        $rules = [];
        foreach ($results as $rule_data) {
            $rules[] = new Rule(
                $rule_data[ilSrRule::F_ID],
                $rule_data[ilSrRule::F_LHS_TYPE],
                $rule_data[ilSrRule::F_LHS_VALUE],
                $rule_data[ilSrRule::F_OPERATOR],
                $rule_data[ilSrRule::F_RHS_TYPE],
                $rule_data[ilSrRule::F_RHS_VALUE]
            );
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function delete(IRule $rule) : bool
    {
        // nothing to do, rule hasn't been saved to the database yet
        if (null === $rule->getId()) return true;

        $ar_rule = ilSrRule::find($rule->getId());
        if (null !== $ar_rule) {
            $ar_rule->delete();
            return true;
        }

        return false;
    }

    /**
     * transforms an active-record rule into a DTO.
     * @param ilSrRule $ar_rule
     * @return Rule
     */
    private function transformToEntity(ilSrRule $ar_rule) : Rule
    {
        return new Rule(
            $ar_rule->getId(),
            $ar_rule->getLhsType(),
            $ar_rule->getLhsValue(),
            $ar_rule->getOperator(),
            $ar_rule->getRhsType(),
            $ar_rule->getRhsValue()
        );
    }

    /**
     * transforms an active-record rule into an array.
     * @param ilSrRule $ar_rule
     * @return array
     */
    private function transformToArrayData(ilSrRule $ar_rule) : array
    {
        return  [
            ilSrRule::F_ID => $ar_rule->getId(),
            ilSrRule::F_LHS_TYPE => $ar_rule->getLhsType(),
            ilSrRule::F_LHS_VALUE => $ar_rule->getLhsValue(),
            ilSrRule::F_OPERATOR => $ar_rule->getOperator(),
            ilSrRule::F_RHS_TYPE => $ar_rule->getRhsType(),
            ilSrRule::F_RHS_VALUE => $ar_rule->getRhsValue(),
        ];
    }
}