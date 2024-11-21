<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormDirector
{
    /**
     * @param RuleFormBuilder $form_builder
     */
    public function __construct(protected RuleFormBuilder $form_builder)
    {
    }

    /**
     * Returns the rule form that corresponds to the give routine
     * requirement type.
     *
     * @param IRoutine $routine
     * @return UIForm
     */
    public function getFormByRoutine(IRoutine $routine): UIForm
    {
        return match ($routine->getRoutineType()) {
            IRoutine::ROUTINE_TYPE_COURSE => $this->getCourseAttributeForm(),
            IRoutine::ROUTINE_TYPE_GROUP => $this->getGroupAttributeForm(),
            IRoutine::ROUTINE_TYPE_SURVEY => $this->getSurveyAttributeForm(),
            default => throw new LogicException(
                self::class . " cannot yet build form for '" . $routine->getRoutineType() . "'."
            ),
        };
    }

    /**
     * Returns a rule form for course- and common-attributes.
     *
     * @return UIForm
     */
    public function getCourseAttributeForm(): UIForm
    {
        return $this->form_builder
            ->addCommonAttributes()
            ->addCourseAttributes()
            ->addObjectAttributes()
            ->addParticipantAttributes()
            ->getForm();
    }

    /**
     * Returns a rule form for course- and common-attributes.
     *
     * @return UIForm
     */
    public function getGroupAttributeForm(): UIForm
    {
        return $this->form_builder
            ->addCommonAttributes()
            ->addObjectAttributes()
            ->addParticipantAttributes()
            ->getForm();
    }

    /**
     * Returns a rule form for course- and common-attributes.
     *
     * @return UIForm
     */
    public function getSurveyAttributeForm(): UIForm
    {
        return $this->form_builder
            ->addCommonAttributes()
            ->addSurveyAttributes()
            ->addObjectAttributes()
            ->getForm();
    }
}
