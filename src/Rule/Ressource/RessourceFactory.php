<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RessourceFactory
{
    /**
     * @var \ilDBInterface
     */
    protected $database;

    public function __construct(\ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function getRessource(\ilObject $object): IRessource
    {
        switch (true) {
            case ($object instanceof \ilObjCourse):
                return new CourseRessource($this->database, $object);
            case ($object instanceof \ilObjSurvey):
                return new SurveyRessource($this->database, $object);
            case ($object instanceof \ilObjGroup):
                return new GroupRessource($this->database, $object);

            default:
                return new NoRessource();
        }
    }
}
