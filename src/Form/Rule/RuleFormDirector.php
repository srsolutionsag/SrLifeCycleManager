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
     * @var RuleFormBuilder
     */
    protected $form_builder;

    /**
     * @param RuleFormBuilder $form_builder
     */
    public function __construct(RuleFormBuilder $form_builder)
    {
        $this->form_builder = $form_builder;
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
        switch ($routine->getRoutineType()) {
            case IRoutine::ROUTINE_TYPE_COURSE:
                return $this->getCourseAttributeForm();

            case IRoutine::ROUTINE_TYPE_GROUP:
                return $this->getGroupAttributeForm();

            case IRoutine::ROUTINE_TYPE_SURVEY:
                return $this->getSurveyAttributeForm();

            default:
                throw new LogicException(
                    self::class . " cannot yet build form for '" . $routine->getRoutineType() . "'."
                );
        }
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
