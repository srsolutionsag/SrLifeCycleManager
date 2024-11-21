<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class SurveyAttribute implements IAttribute
{
    public function __construct(protected \ilObjSurvey $survey)
    {
    }

    protected function getParticipants(): array
    {
        return ($this->survey->get360Mode()) ?
            $this->survey->getAppraiseesData() :
            $this->survey->getSurveyParticipants(null, false, true);
    }
}
