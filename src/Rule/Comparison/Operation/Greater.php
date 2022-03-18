<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractSizeComparison;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Greater extends AbstractSizeComparison
{
    /**
     * @inheritdoc
     */
    protected function compare($lhs_value, $rhs_value) : bool
    {
        if ($this->strict) {
            return ($lhs_value > $rhs_value);
        }

        return ($lhs_value >= $rhs_value);
    }
}