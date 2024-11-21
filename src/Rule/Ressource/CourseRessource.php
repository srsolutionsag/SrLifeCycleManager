<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseRessource implements IDatabaseRessource, IParticipantRessource, IObjectRessource
{
    protected \ilDBInterface $database;

    protected \ilObjCourse $course;

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
