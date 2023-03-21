<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\SurveyRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyParticipants;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyQuestions;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyResults;

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
