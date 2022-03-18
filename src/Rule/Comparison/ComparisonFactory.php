<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Equal;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Greater;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\InArray;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\InValue;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Lesser;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Unequal;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use LogicException;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ComparisonFactory
{
    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var RequirementFactory
     */
    protected $requirement_factory;

    /**
     * @param RequirementFactory $requirement_factory
     * @param AttributeFactory   $attribute_factory
     */
    public function __construct(RequirementFactory $requirement_factory, AttributeFactory $attribute_factory)
    {
        $this->requirement_factory = $requirement_factory;
        $this->attribute_factory = $attribute_factory;
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @return IComparison
     */
    public function getComparison(ilObject $object, IRule $rule) : IComparison
    {
        switch ($rule->getOperator()) {
            case IRule::OPERATOR_EQUAL:
                return $this->equal($object, $rule);

            case IRule::OPERATOR_NOT_EQUAL:
                return $this->unequal($object, $rule);

            case IRule::OPERATOR_GREATER:
                return $this->greater($object, $rule, true);

            case IRule::OPERATOR_GREATER_EQUAL:
                return $this->greater($object, $rule, false);

            case IRule::OPERATOR_LESSER:
                return $this->lesser($object, $rule, true);

            case IRule::OPERATOR_LESSER_EQUAL:
                return $this->lesser($object, $rule, false);

            case IRule::OPERATOR_CONTAINS:
                return $this->inValue($object, $rule);

            case IRule::OPERATOR_IN_ARRAY:
                return $this->inArray($object, $rule);

            default:
                throw new LogicException("Comparison for operator '{$rule->getOperator()}' is not supported.");
        }
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @return Equal
     */
    public function equal(ilObject $object, IRule $rule) : Equal
    {
        return new Equal(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule
        );
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @return Unequal
     */
    public function unequal(ilObject $object, IRule $rule) : Unequal
    {
        return new Unequal(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule
        );
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @param bool     $strict
     * @return Greater
     */
    public function greater(ilObject $object, IRule $rule, bool $strict) : Greater
    {
        return new Greater(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule,
            $strict
        );
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @param bool     $strict
     * @return Lesser
     */
    public function lesser(ilObject $object, IRule $rule, bool $strict) : Lesser
    {
        return new Lesser(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule,
            $strict
        );
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @return InValue
     */
    public function inValue(ilObject $object, IRule $rule) : InValue
    {
        return new InValue(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule
        );
    }

    /**
     * @param ilObject $object
     * @param IRule    $rule
     * @return InArray
     */
    public function inArray(ilObject $object, IRule $rule) : InArray
    {
        return new InArray(
            $this->attribute_factory,
            $this->requirement_factory->getRequirement($object),
            $rule
        );
    }
}