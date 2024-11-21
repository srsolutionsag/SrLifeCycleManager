<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant\ParticipantAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AttributeFactory
{
    public function __construct(
        protected CommonAttributeFactory $common_factory,
        protected ParticipantAttributeFactory $participant_factory,
        protected ObjectAttributeFactory $object_factory,
        protected SurveyAttributeFactory $survey_factory,
        protected CourseAttributeFactory $course_factory
    ) {
    }

    public function getAttribute(IRessource $ressource, string $type, string $value): IAttribute
    {
        return match ($type) {
            $this->participant_factory->getAttributeType() => $this->participant_factory->getAttribute(
                $ressource,
                $value
            ),
            $this->object_factory->getAttributeType() => $this->object_factory->getAttribute($ressource, $value),
            $this->course_factory->getAttributeType() => $this->course_factory->getAttribute($ressource, $value),
            $this->survey_factory->getAttributeType() => $this->survey_factory->getAttribute($ressource, $value),
            default => $this->common_factory->getAttribute($type, $value),
        };
    }

    /**
     * @return string[]
     */
    public function getAttributeValues(string $type): array
    {
        return match ($type) {
            $this->object_factory->getAttributeType() => $this->object_factory->getAttributeValues(),
            $this->course_factory->getAttributeType() => $this->course_factory->getAttributeValues(),
            $this->survey_factory->getAttributeType() => $this->survey_factory->getAttributeValues(),
            $this->participant_factory->getAttributeType() => $this->participant_factory->getAttributeValues(),
            default => $this->common_factory->getAttributeValues(),
        };
    }
}
