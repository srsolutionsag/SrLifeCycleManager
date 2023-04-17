<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SurveyRessource implements IDatabaseRessource, IObjectRessource
{
    /**
     * @var \ilDBInterface
     */
    protected $database;

    /**
     * @var \ilObjSurvey
     */
    protected $survey;

    public function __construct(\ilDBInterface $database, \ilObjSurvey $survey)
    {
        $this->database = $database;
        $this->survey = $survey;
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
