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

use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectAge extends ObjectAttribute
{
    use DateTimeHelper;

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     * @return int|string|null
     */
    public function getComparableValue(string $type): null|int|string
    {
        $creation_date = $this->getDateTime($this->getObject()->getCreateDate());
        if (null === $creation_date) {
            return null;
        }

        $elapsed_days = $this->getGap($creation_date, $this->getCurrentDate());

        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_INT => $elapsed_days,
            self::COMPARABLE_VALUE_TYPE_STRING => (string) $elapsed_days,
            default => null,
        };
    }
}
