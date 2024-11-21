<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

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
    public function isApplicable(): bool
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
