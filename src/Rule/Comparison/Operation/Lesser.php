<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\AbstractSizeComparison;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Lesser extends AbstractSizeComparison
{
    /**
     * @inheritDoc
     */
    protected function compare($lhs_value, $rhs_value) : bool
    {
        if ($this->strict) {
            return ($lhs_value < $rhs_value);
        }

        return ($lhs_value <= $rhs_value);
    }
}