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
    protected CommonAttributeFactory $common_factory;

    protected ParticipantAttributeFactory $participant_factory;

    protected ObjectAttributeFactory $object_factory;

    protected SurveyAttributeFactory $survey_factory;

    protected CourseAttributeFactory $course_factory;

    public function __construct(
        CommonAttributeFactory $common_factory,
        ParticipantAttributeFactory $participant_factory,
        ObjectAttributeFactory $object_factory,
        SurveyAttributeFactory $survey_factory,
        CourseAttributeFactory $course_factory
    ) {
        $this->common_factory = $common_factory;
        $this->participant_factory = $participant_factory;
        $this->object_factory = $object_factory;
        $this->survey_factory = $survey_factory;
        $this->course_factory = $course_factory;
    }

    public function getAttribute(IRessource $ressource, string $type, string $value): IAttribute
    {
        switch ($type) {
            case $this->participant_factory->getAttributeType():
                return $this->participant_factory->getAttribute($ressource, $value);
            case $this->object_factory->getAttributeType():
                return $this->object_factory->getAttribute($ressource, $value);
            case $this->course_factory->getAttributeType():
                return $this->course_factory->getAttribute($ressource, $value);
            case $this->survey_factory->getAttributeType():
                return $this->survey_factory->getAttribute($ressource, $value);

            default:
                return $this->common_factory->getAttribute($type, $value);
        }
    }

    /**
     * @return string[]
     */
    public function getAttributeValues(string $type): array
    {
        switch ($type) {
            case $this->object_factory->getAttributeType():
                return $this->object_factory->getAttributeValues();
            case $this->course_factory->getAttributeType():
                return $this->course_factory->getAttributeValues();
            case $this->survey_factory->getAttributeType():
                return $this->survey_factory->getAttributeValues();
            case $this->participant_factory->getAttributeType():
                return $this->participant_factory->getAttributeValues();

            case $this->common_factory->getAttributeType():

            default:
                return $this->common_factory->getAttributeValues();
        }
    }
}
