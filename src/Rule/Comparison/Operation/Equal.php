<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractComparison;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Equal extends AbstractComparison
{
    /**
     * @inheritDoc
     */
    public function isApplicable() : bool
    {
        $comparable_type = $this->getSimilarValueType();

        if (null === $comparable_type) {
            return false;
        }

        return (
            $this->lhs_attribute->getComparableValue($comparable_type) ===
            $this->rhs_attribute->getComparableValue($comparable_type)
        );
    }
}