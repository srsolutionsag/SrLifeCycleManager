<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Dependency;

use LogicException;
use ilDBInterface;
use ilObjGroup;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DependencyPool implements IDependencyPool, IDatabaseReliable, ICourseReliable, IGroupReliable
{
    /**
     * @var ilDBInterface|null
     */
    protected $database;

    /**
     * @var ilObjCourse|null
     */
    protected $course;

    /**
     * @var ilObjGroup|null
     */
    protected $group;

    /**
     * @param ilDBInterface|null $database
     * @param ilObjCourse|null   $course
     * @param ilObjCourse|null   $group
     */
    public function __construct(
        ?ilDBInterface $database = null,
        ?ilObjCourse $course = null,
        ?ilObjCourse $group = null
    ) {
        $this->database = $database;
        $this->course = $course;
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function getDatabase() : ilDBInterface
    {
        if (null === $this->database) {
            throw new LogicException("Dependency " . ilDBInterface::class . " was required but never injected.");
        }

        return $this->database;
    }

    /**
     * @inheritDoc
     */
    public function getCourse() : ilObjCourse
    {
        if (null === $this->course) {
            throw new LogicException("Dependency " . ilObjCourse::class . " was required but never injected.");
        }

        return $this->course;
    }

    /**
     * @inheritDoc
     */
    public function getGroup() : ilObjGroup
    {
        if (null === $this->group) {
            throw new LogicException("Dependency " . ilObjGroup::class . " was required but never injected.");
        }

        return $this->group;
    }
}