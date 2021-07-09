<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\ResolverFactory;

/**
 * Class AbstractComparison
 * @package srag\Plugins\SrLifeCycleManager\Rule\Evaluation
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class AbstractComparison implements IComparison
{
    /**
     * @var IRule;
     */
    private $rule;

    /**
     * @var ResolverFactory
     */
    private $factory;

    /**
     * AbstractComparison constructor.
     * 
     * @param IRule $rule
     */
    public function __construct(IRule $rule)
    {
        $this->rule    = $rule;
        $this->factory = ResolverFactory::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function getRule() : IRule
    {
        return $this->rule;
    }

    /**
     * @inheritDoc
     */
    public function compare() : bool
    {
        $lhs_value = $this->factory->getResolverForType($this->rule->getLhsType())->resolveLhsValue($this);
        $rhs_value = $this->factory->getResolverForType($this->rule->getRhsType())->resolveRhsValue($this);

        switch ($this->rule->getOperator()) {
            case IRule::OPERATOR_EQUAL:
                return ($lhs_value === $rhs_value);
            case IRule::OPERATOR_NOT_EQUAL:
                return ($lhs_value !== $rhs_value);
            case IRule::OPERATOR_GREATER:
                return ($lhs_value > $rhs_value);
            case IRule::OPERATOR_LESSER:
                return ($lhs_value < $rhs_value);
            case IRule::OPERATOR_GREATER_EQUAL:
                return ($lhs_value >= $rhs_value);
            case IRule::OPERATOR_LESSER_EQUAL:
                return ($lhs_value <= $rhs_value);
            case IRule::OPERATOR_CONTAINS:
                return $this->handleInValueComparison($lhs_value, $rhs_value);
            case IRule::OPERATOR_IN_ARRAY:
                return $this->handleInArrayComparison($lhs_value, $rhs_value);

            default:
                throw new \LogicException("Operator '{$this->rule->getOperator()}' is not yet consieder by comparison: " . self::class);
        }
    }

    /**
     * @param mixed $lhs_value
     * @param mixed $rhs_value
     * @return bool
     */
    private function handleInValueComparison($lhs_value, $rhs_value) : bool
    {
        // cast values to string
        $lhs_value = (string) $lhs_value;
        $rhs_value = (string) $rhs_value;
        // explode values at all whitespace characters and commas
        $lhs_pieces = preg_split("/[\s,]+/", $lhs_value);
        $rhs_pieces = preg_split("/[\s,]+/", $rhs_value);

        // get similarities of exploded value pieces
        $similarities =  array_intersect($lhs_pieces, $rhs_pieces);

        // returns true if at least one similarity was found
        return (1 <= count($similarities));
    }

    /**
     * @param mixed $lhs_value
     * @param mixed $rhs_value
     * @return bool
     */
    private function handleInArrayComparison($lhs_value, $rhs_value) : bool
    {
        if (!is_array($lhs_value) && !is_array($rhs_value)) {
            // at least one given value must be an array for this comparison
            return false;
        }

        if (is_array($lhs_value) && is_array($rhs_value)) {
            // returns true if both values are an array and similarities were found
            $similarities = array_intersect($lhs_value, $rhs_value);
            return (1 <= count($similarities));
        }

        // at this point at least one value is an array, hence no further checks
        if (is_array($lhs_value)) {
            $array = $lhs_value;
            $search = $rhs_value;
        } else {
            $array = $rhs_value;
            $search = $lhs_value;
        }

        // returns true if the one value was found in the other one
        return in_array($search, $array);
    }
}