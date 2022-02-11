<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupTitle extends GroupAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
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
            return $this->group->getTitle();
        }

        return null;
    }
}