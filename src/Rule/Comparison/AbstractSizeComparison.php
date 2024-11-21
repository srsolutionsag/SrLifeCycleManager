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
    public function __construct(
        AttributeFactory $attribute_factory,
        IRessource $ressource,
        IRule $rule,
        protected bool $strict
    ) {
        parent::__construct($attribute_factory, $ressource, $rule);
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

        return match ($comparable_type) {
            IAttribute::COMPARABLE_VALUE_TYPE_STRING => $this->compare(strlen((string) $lhs), strlen((string) $rhs)),
            IAttribute::COMPARABLE_VALUE_TYPE_ARRAY => $this->compare(count($lhs), count($rhs)),
            default => $this->compare($lhs, $rhs),
        };
    }

    /**
     * Compares the given values with the greater- or lesser-than operator.
     *
     * @return bool
     */
    abstract protected function compare(mixed $lhs_value, mixed $rhs_value): bool;
}
