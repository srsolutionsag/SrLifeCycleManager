<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractSizeComparison extends AbstractComparison
{
    /**
     * @var bool
     */
    protected $strict;

    /**
     * @param AttributeFactory $attribute_factory
     * @param IRequirement     $requirement
     * @param IRule            $rule
     * @param bool             $strict
     */
    public function __construct(
        AttributeFactory $attribute_factory,
        IRequirement $requirement,
        IRule $rule,
        bool $strict
    ) {
        parent::__construct($attribute_factory, $requirement, $rule);
        $this->strict = $strict;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable() : bool
    {
        $comparable_type = $this->getSimilarValueType();

        if (null === $comparable_type) {
            return false;
        }

        $lhs = $this->lhs_attribute->getComparableValue($comparable_type);
        $rhs = $this->rhs_attribute->getComparableValue($comparable_type);

        switch ($comparable_type) {
            case IAttribute::COMPARABLE_VALUE_TYPE_STRING:
                return $this->compare(strlen($lhs), strlen($rhs));

            case IAttribute::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->compare(count($lhs), count($rhs));

            default:
                return $this->compare($lhs, $rhs);
        }
    }

    /**
     * Compares the given values with the greater- or lesser-than operator.
     *
     * @param mixed $lhs_value
     * @param mixed $rhs_value
     * @return bool
     */
    abstract protected function compare($lhs_value, $rhs_value) : bool;
}