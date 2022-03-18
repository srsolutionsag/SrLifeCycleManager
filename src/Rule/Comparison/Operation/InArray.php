<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class InArray extends AbstractComparison
{
    /**
     * @inheritDoc
     */
    public function isApplicable() : bool
    {
        $lhs_types = $this->lhs_attribute->getComparableValueTypes();
        $rhs_types = $this->rhs_attribute->getComparableValueTypes();

        // abort if one of the comparable value types is not defined.
        if (empty($lhs_types) || empty($rhs_types)) {
            return false;
        }

        // this operation needs the lhs value to be comparable as array.
        if (!in_array(IAttribute::COMPARABLE_VALUE_TYPE_ARRAY, $rhs_types, true)) {
            return false;
        }

        $lhs_type = $this->getLhsType($lhs_types);
        if (null === $lhs_type) {
            return false;
        }

        $lhs = $this->lhs_attribute->getComparableValue($lhs_type);
        $rhs = $this->rhs_attribute->getComparableValue(IAttribute::COMPARABLE_VALUE_TYPE_ARRAY);

        return in_array($lhs, $rhs, false);
    }

    /**
     * @param array $lhs_types
     * @return string
     */
    protected function getLhsType(array $lhs_types) : ?string
    {
        foreach ($lhs_types as $type) {
            if (IAttribute::COMPARABLE_VALUE_TYPE_ARRAY !== $type) {
                return $type;
            }
        }

        return null;
    }
}