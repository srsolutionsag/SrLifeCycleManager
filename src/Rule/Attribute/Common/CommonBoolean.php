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
class CommonBoolean extends CommonAttribute
{
    public function __construct(protected bool $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_BOOL,
            self::COMPARABLE_VALUE_TYPE_STRING,
            self::COMPARABLE_VALUE_TYPE_INT,
        ];
    }

    /**
     * @inheritDoc
     * @return bool|int|string|null
     */
    public function getComparableValue(string $type): bool|string|int|null
    {
        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_BOOL => $this->value,
            self::COMPARABLE_VALUE_TYPE_STRING => ($this->value) ? 'true' : 'false',
            self::COMPARABLE_VALUE_TYPE_INT => ($this->value) ? 1 : 0,
            default => null,
        };
    }
}
