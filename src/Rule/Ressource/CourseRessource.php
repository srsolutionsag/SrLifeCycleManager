<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseRessource implements IDatabaseRessource, IParticipantRessource, IObjectRessource
{
    /**
     * @var \ilDBInterface
     */
    protected $database;

    /**
     * @var \ilObjCourse
     */
    protected $course;

    public function __construct(\ilDBInterface $database, \ilObjCourse $course)
    {
        $this->database = $database;
        $this->course = $course;
    }

    /**
     * @inheritDoc
     */
    public function getDatabase(): \ilDBInterface
    {
        return $this->database;
    }

    /**
     * @inheritDoc
     */
    public function getObject(): \ilObject
    {
        return $this->course;
    }

    /**
     * @inheritDoc
     */
    public function getParticipants(): \ilParticipants
    {
        return $this->course->getMembersObject();
    }

    public function getCourse(): \ilObjCourse
    {
        return $this->course;
    }
}
