<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractSizeComparison extends AbstractComparison
{
    /**
     * @var bool
     */
    protected $strict;

    public function __construct(
        AttributeFactory $attribute_factory,
        IRessource $ressource,
        IRule $rule,
        bool $strict
    ) {
        parent::__construct($attribute_factory, $ressource, $rule);
        $this->strict = $strict;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(): bool
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
    abstract protected function compare($lhs_value, $rhs_value): bool;
}