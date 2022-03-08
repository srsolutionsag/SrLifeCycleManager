<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course\ICourseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course\CourseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group\IGroupRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group\GroupRequirement;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use LogicException;
use ilDBInterface;
use ilObject2;
use ilObject;
use ilObjCourse;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RequirementFactory
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @param ilObject $object
     * @return IRequirement
     */
    public function getRequirement(ilObject $object) : IRequirement
    {
        $type = ilObject2::_lookupType($object->getRefId(), true);
        switch ($type) {
            case IRoutine::ROUTINE_TYPE_COURSE:
                /** @var $object ilObjCourse */
                return $this->course($object);

            case IRoutine::ROUTINE_TYPE_GROUP:
                /** @var $object ilObjGroup */
                return $this->group($object);

            default:
                throw new LogicException("Requirement for given object of type '$type' is not supported.");
        }
    }

    /**
     * @param ilObjCourse $course
     * @return ICourseRequirement
     */
    public function course(ilObjCourse $course) : ICourseRequirement
    {
        return new CourseRequirement($this->database, $course);
    }

    /**
     * @param ilObjGroup $group
     * @return IGroupRequirement
     */
    public function group(ilObjGroup $group) : IGroupRequirement
    {
        return new GroupRequirement($this->database, $group);
    }
}