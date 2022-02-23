<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course;

use LogicException;
use ilDBInterface;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseRequirement implements ICourseRequirement
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
     * @param ilDBInterface|null $database
     * @param ilObjCourse|null   $course
     */
    public function __construct(
        ilDBInterface $database = null,
        ilObjCourse $course = null
    ) {
        $this->database = $database;
        $this->course = $course;
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
}