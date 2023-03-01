<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\MetadataAttribute;
use ilDBInterface;
use ilObjCourse;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseMetadata extends MetadataAttribute
{
    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var ilObjCourse
     */
    private $object;

    public function __construct(ilDBInterface $database, ilObjCourse $course)
    {
        $this->database = $database;
        $this->object = $course;
    }

    /**
     * @inheritDoc
     */
    protected function getDatabase(): ilDBInterface
    {
        return $this->database;
    }

    /**
     * @inheritDoc
     */
    protected function getObject(): ilObject
    {
        return $this->object;
    }
}