<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group\IGroupRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupAttributeFactory
{
    /**
     * @param IGroupRequirement $requirement
     * @param string            $value
     * @return IAttribute
     */
    public function getAttribute(IGroupRequirement $requirement, string $value) : IAttribute
    {
        switch ($value) {
            case GroupTitle::class:
                return new GroupTitle($requirement->getGroup());

            case GroupMember::class:
                return new GroupMember($requirement->getGroup());

            case GroupCreation::class:
                return new GroupCreation($requirement->getGroup());

            case GroupAge::class:
                return new GroupAge($requirement->getGroup());

            case GroupMetadata::class:
                return new GroupMetadata($requirement->getDatabase(), $requirement->getGroup());

            case GroupTaxonomy::class:
                return new GroupTaxonomy($requirement->getDatabase(), $requirement->getGroup());

            default:
                return new CommonNull($value);
        }
    }

    /**
     * @return string[]
     */
    public function getAttributeList() : array
    {
        return [
            GroupTitle::class,
            GroupMember::class,
            GroupMetadata::class,
            GroupTaxonomy::class,
            GroupCreation::class,
            GroupAge::class,
        ];
    }
}