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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonString extends CommonAttribute
{
    public function __construct(protected string $value)
    {
    }

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
            return $this->value;
        }

        return null;
    }
}
