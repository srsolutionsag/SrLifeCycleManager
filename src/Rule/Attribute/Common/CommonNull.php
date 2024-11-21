<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonNull extends CommonAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_NULL,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        return null;
    }
}
