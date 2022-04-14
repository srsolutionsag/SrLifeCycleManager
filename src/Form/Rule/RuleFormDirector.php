<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

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
    public function getFormByRoutine(IRoutine $routine) : UIForm
    {
        switch ($routine->getRoutineType()) {
            case IRoutine::ROUTINE_TYPE_COURSE:
                return $this->getCourseAttributeForm();

            case IRoutine::ROUTINE_TYPE_GROUP:
                return $this->getGroupAttributeForm();

            default:
                throw new LogicException(self::class . " cannot yet build form for '" . $routine->getRoutineType() . "'.");
        }
    }

    /**
     * Returns a rule form for course- and common-attributes.
     *
     * @return UIForm
     */
    public function getCourseAttributeForm() : UIForm
    {
        return $this->form_builder
            ->addCommonAttributes()
            ->addCourseAttributes()
            ->getForm()
        ;
    }

    /**
     * Returns a rule form for course- and common-attributes.
     *
     * @return UIForm
     */
    public function getGroupAttributeForm() : UIForm
    {
        return $this->form_builder
            ->addCommonAttributes()
            ->addGroupAttributes()
            ->getForm()
        ;
    }
}