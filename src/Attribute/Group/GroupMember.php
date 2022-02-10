<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilObjGroup;
use ilCourseParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupMember extends GroupAttribute
{
    /**
     * @var ilCourseParticipants|null
     */
    protected $members;

    /**
     * @param ilObjGroup $group
     */
    public function __construct(ilObjGroup $group)
    {
        parent::__construct($group);

        $this->members = $group->getMembersObject();
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_ARRAY,
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        if (null === $this->members) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->members->getMembers();

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $this->members->getCountMembers();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->members->getMembers());

            default:
                return null;
        }
    }
}