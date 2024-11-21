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

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\SurveyRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SurveyAttributeFactory implements IDynamicAttributeProvider
{
    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return SurveyAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [
            SurveyParticipants::class,
            SurveyQuestions::class,
            SurveyResults::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute
    {
        if (!$ressource instanceof SurveyRessource) {
            return new CommonNull();
        }

        switch ($value) {
            case SurveyParticipants::class:
                return new SurveyParticipants($ressource->getSurvey());
            case SurveyQuestions::class:
                return new SurveyQuestions($ressource->getSurvey());
            case SurveyResults::class:
                return new SurveyResults($ressource->getSurvey());

            default:
                return new CommonNull();
        }
    }
}
