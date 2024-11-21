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
class CommonList extends CommonAttribute
{
    public function __construct(protected array $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_ARRAY,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     * @return mixed[]|string|null
     */
    public function getComparableValue(string $type): array|string|null
    {
        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_ARRAY => $this->value,
            self::COMPARABLE_VALUE_TYPE_STRING => implode(',', $this->value),
            default => null,
        };
    }
}
