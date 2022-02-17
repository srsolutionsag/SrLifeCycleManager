<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * IRule describes the DAO of a rule.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRule
{
    public const F_RULE_ID   = 'rule_id';
    public const F_RHS_TYPE  = 'rhs_type';
    public const F_RHS_VALUE = 'rhs_value';
    public const F_OPERATOR  = 'operator';
    public const F_LHS_TYPE  = 'lhs_type';
    public const F_LHS_VALUE = 'lhs_value';

    public const OPERATOR_EQUAL            = 'operator_equal';
    public const OPERATOR_NOT_EQUAL        = 'operator_not_equal';
    public const OPERATOR_GREATER          = 'operator_greater';
    public const OPERATOR_GREATER_EQUAL    = 'operator_greater_equal';
    public const OPERATOR_LESSER           = 'operator_lesser';
    public const OPERATOR_LESSER_EQUAL     = 'operator_lesser_equal';
    public const OPERATOR_CONTAINS         = 'operator_in_value';
    public const OPERATOR_IN_ARRAY         = 'operator_in_array';

    /**
     * @var string[] possible operators as string
     */
    public const OPERATORS = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_GREATER,
        self::OPERATOR_GREATER_EQUAL,
        self::OPERATOR_LESSER,
        self::OPERATOR_LESSER_EQUAL,
        self::OPERATOR_CONTAINS,
        self::OPERATOR_IN_ARRAY,
    ];

    /**
     * @return int|null
     */
    public function getRuleId() : ?int;

    /**
     * @param int|null $rule_id
     * @return IRule
     */
    public function setRuleId(?int $rule_id) : IRule;

    /**
     * @return string
     */
    public function getLhsType() : string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setLhsType(string $type) : IRule;

    /**
     * @return mixed
     */
    public function getLhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setLhsValue($value) : IRule;

    /**
     * @return string
     */
    public function getOperator() : string;

    /**
     * @param string $operator
     * @return IRule
     */
    public function setOperator(string $operator) : IRule;

    /**
     * @return string
     */
    public function getRhsType() : string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setRhsType(string $type) : IRule;

    /**
     * @return mixed
     */
    public function getRhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setRhsValue($value) : IRule;
}