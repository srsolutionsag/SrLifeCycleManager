<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectTitle extends ObjectAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type): ?string
    {
        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return $this->getObject()->getTitle();
        }

        return null;
    }
}
