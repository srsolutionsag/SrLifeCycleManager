<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use ilObjSurvey;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class SurveyAttribute implements IAttribute
{
    /**
     * @var ilObjSurvey
     */
    protected $survey;

    public function __construct(ilObjSurvey $survey)
    {
        $this->survey = $survey;
    }

    protected function getParticipants(): array
    {
        return ($this->survey->get360Mode()) ?
            $this->survey->getAppraiseesData() :
            $this->survey->getSurveyParticipants(null, false, true);
    }
}
