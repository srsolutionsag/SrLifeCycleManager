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
class SurveyRessource implements IDatabaseRessource, IObjectRessource
{
    public function __construct(protected \ilDBInterface $database, protected \ilObjSurvey $survey)
    {
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
        return $this->survey;
    }

    public function getSurvey(): \ilObjSurvey
    {
        return $this->survey;
    }
}
