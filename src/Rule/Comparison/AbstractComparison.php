<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractComparison implements IComparison
{
    /**
     * @var IAttribute
     */
    protected $lhs_attribute;

    /**
     * @var IAttribute
     */
    protected $rhs_attribute;

    /**
     * @var IRule
     */
    protected $rule;

    /**
     * @param AttributeFactory $attribute_factory
     * @param IRequirement     $requirement
     * @param IRule            $rule
     */
    public function __construct(AttributeFactory $attribute_factory, IRequirement $requirement, IRule $rule)
    {
        $this->rule = $rule;
        $this->lhs_attribute = $attribute_factory->getAttribute(
            $requirement,
            $rule->getLhsType(),
            $rule->getLhsValue()
        );

        $this->rhs_attribute = $attribute_factory->getAttribute(
            $requirement,
            $rule->getRhsType(),
            $rule->getRhsValue()
        );
    }

    /**
     * @return string|null
     */
    protected function getSimilarValueType() : ?string
    {
        $similarities = array_intersect(
            $this->lhs_attribute->getComparableValueTypes(),
            $this->rhs_attribute->getComparableValueTypes()
        );

        if (empty($similarities) ||
            !in_array($similarities[0], IAttribute::COMPARABLE_VALUE_TYPES, true)
        ) {
            return null;
        }

        return $similarities[0];
    }
}