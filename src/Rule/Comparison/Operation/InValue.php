<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class InValue extends AbstractComparison
{
    /**
     * @inheritDoc
     */
    public function isApplicable() : bool
    {
        $lhs_types = $this->lhs_attribute->getComparableValueTypes();
        $rhs_types = $this->rhs_attribute->getComparableValueTypes();

        // abort if not both values are comparable as string values.
        if (!in_array(IAttribute::COMPARABLE_VALUE_TYPE_STRING, $lhs_types, true) ||
            !in_array(IAttribute::COMPARABLE_VALUE_TYPE_STRING, $rhs_types, true)
        ) {
            return false;
        }

        $lhs = $this->lhs_attribute->getComparableValue(IAttribute::COMPARABLE_VALUE_TYPE_STRING);
        $rhs = $this->rhs_attribute->getComparableValue(IAttribute::COMPARABLE_VALUE_TYPE_STRING);

        return (false !== strpos($rhs, $lhs));
    }
}