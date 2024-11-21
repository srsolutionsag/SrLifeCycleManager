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
class RessourceFactory
{
    protected \ilDBInterface $database;

    public function __construct(\ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function getRessource(\ilObject $object): IRessource
    {
        if ($object instanceof \ilObjCourse) {
            return new CourseRessource($this->database, $object);
        }
        if ($object instanceof \ilObjSurvey) {
            return new SurveyRessource($this->database, $object);
        }
        if ($object instanceof \ilObjGroup) {
            return new GroupRessource($this->database, $object);
        }
        return new NoRessource();
    }
}
