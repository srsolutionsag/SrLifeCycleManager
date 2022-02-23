<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Comparison implements IComparison
{
    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var IRequirement
     */
    protected $requirement;

    /**
     * @var IRule
     */
    protected $rule;

    /**
     * @param AttributeFactory $attribute_factory
     * @param IRequirement     $requirement
     * @param IRule            $rule
     */
    public function __construct(
        AttributeFactory $attribute_factory,
        IRequirement $requirement,
        IRule $rule
    ) {
        $this->attribute_factory = $attribute_factory;
        $this->requirement = $requirement;
        $this->rule = $rule;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable() : bool
    {
        $lhs_attribute = $this->getLhsAttribute();
        $rhs_attribute = $this->getRhsAttribute();

        $comparable_type = $this->findComparableValueTypes(
            $lhs_attribute,
            $rhs_attribute
        );

        if (null === $comparable_type) {
            return false;
        }

        $lhs_value = $lhs_attribute->getComparableValue($comparable_type);
        $rhs_value = $rhs_attribute->getComparableValue($comparable_type);

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
                return false;
        }
    }

    /**
     * @param IAttribute $lhs_attribute
     * @param IAttribute $rhs_attribute
     * @return string|null
     */
    protected function findComparableValueTypes(IAttribute $lhs_attribute, IAttribute $rhs_attribute) : ?string
    {
        $similarities = array_intersect(
            $lhs_attribute->getComparableValueTypes(),
            $rhs_attribute->getComparableValueTypes()
        );

        if (1 < count($similarities)) {
            return null;
        }

        return $similarities[0];
    }

    /**
     * @param mixed $lhs_value
     * @param mixed $rhs_value
     * @return bool
     */
    protected function handleInValueComparison($lhs_value, $rhs_value) : bool
    {
        // cast values to string
        $lhs_value = (string) $lhs_value;
        $rhs_value = (string) $rhs_value;
        // explode values at all whitespace characters and commas
        $lhs_pieces = preg_split("/[\s,]+/", $lhs_value);
        $rhs_pieces = preg_split("/[\s,]+/", $rhs_value);

        // get similarities of exploded value pieces
        $similarities =  array_intersect($lhs_pieces, $rhs_pieces);

        // @TODO: might check the length of the similarity as well.
        // @TODO: might ignore certain similarities like 'the', 'a', '&'.

        // returns true if at least one similarity was found
        return (1 <= count($similarities));
    }

    /**
     * @param mixed $lhs_value
     * @param mixed $rhs_value
     * @return bool
     */
    protected function handleInArrayComparison($lhs_value, $rhs_value) : bool
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
        return in_array($search, $array, true);
    }

    /**
     * @return IAttribute
     */
    protected function getLhsAttribute() : IAttribute
    {
        return $this->attribute_factory->getAttribute(
            $this->requirement,
            $this->rule->getLhsType(),
            $this->rule->getLhsValue()
        );
    }

    /**
     * @return IAttribute
     */
    protected function getRhsAttribute() : IAttribute
    {
        return $this->attribute_factory->getAttribute(
            $this->requirement,
            $this->rule->getRhsType(),
            $this->rule->getRhsValue()
        );
    }
}