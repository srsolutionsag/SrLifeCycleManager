<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRule
{
    // IRule operators:
    public const OPERATOR_CONTAINS = 'operator_in_value';
    public const OPERATOR_EQUAL = 'operator_equal';
    public const OPERATOR_GREATER = 'operator_greater';
    public const OPERATOR_GREATER_EQUAL = 'operator_greater_equal';
    public const OPERATOR_IN_ARRAY = 'operator_in_array';
    public const OPERATOR_LESSER = 'operator_lesser';
    public const OPERATOR_LESSER_EQUAL = 'operator_lesser_equal';
    public const OPERATOR_NOT_EQUAL = 'operator_not_equal';

    // IRule attributes:
    public const F_LHS_TYPE = 'lhs_type';
    public const F_LHS_VALUE = 'lhs_value';
    public const F_OPERATOR = 'operator';
    public const F_RHS_TYPE = 'rhs_type';
    public const F_RHS_VALUE = 'rhs_value';
    public const F_RULE_ID = 'rule_id';
    public const F_ROUTINE_ID = 'routine_id';

    // IRule equation sides:
    public const RULE_SIDE_RIGHT = 'rhs';
    public const RULE_SIDE_LEFT = 'lhs';

    /**
     * @return int|null
     */
    public function getRuleId(): ?int;

    /**
     * @param int|null $rule_id
     * @return IRule
     */
    public function setRuleId(?int $rule_id): IRule;

    /**
     * @return int
     */
    public function getRoutineId(): int;

    /**
     * @param int $routine_id
     * @return IRule
     */
    public function setRoutineId(int $routine_id): IRule;

    /**
     * @return string
     */
    public function getLhsType(): string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setLhsType(string $type): IRule;

    /**
     * @return mixed
     */
    public function getLhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setLhsValue($value): IRule;

    /**
     * @return string
     */
    public function getOperator(): string;

    /**
     * @param string $operator
     * @return IRule
     */
    public function setOperator(string $operator): IRule;

    /**
     * @return string
     */
    public function getRhsType(): string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setRhsType(string $type): IRule;

    /**
     * @return mixed
     */
    public function getRhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setRhsValue($value): IRule;

    /**
     * Helper function that returns the value-type of the given side.
     *
     * @param string $rule_side (lhs|rhs)
     * @return string|null
     */
    public function getTypeBySide(string $rule_side): ?string;

    /**
     * Helper function that returns the value-type of the given side.
     *
     * @param string $rule_side (lhs|rhs)
     * @return mixed|null
     */
    public function getValueBySide(string $rule_side);
}
