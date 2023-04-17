<?php

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
    public function getComparableValue(string $type)
    {
        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return $this->getObject()->getTitle();
        }

        return null;
    }
}
