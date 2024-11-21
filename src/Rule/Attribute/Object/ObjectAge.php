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

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use ilObject;

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
     */
    public function getComparableValue(string $type)
    {
        $creation_date = $this->getDateTime($this->getObject()->getCreateDate());
        if (null === $creation_date) {
            return null;
        }

        $elapsed_days = $this->getGap($creation_date, $this->getCurrentDate());

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return $elapsed_days;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return (string) $elapsed_days;

            default:
                return null;
        }
    }
}
