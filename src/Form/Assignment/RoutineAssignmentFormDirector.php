<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormDirector
{
    /**
     * @var RoutineAssignmentFormBuilder
     */
    protected $form_builder;

    /**
     * @param RoutineAssignmentFormBuilder $form_builder
     */
    public function __construct(RoutineAssignmentFormBuilder $form_builder)
    {
        $this->form_builder = $form_builder;
    }

    /**
     * Returns the corresponding form depending on the state of the given assignment.
     *
     * @param IRoutineAssignment $assignment
     * @return UIForm
     */
    public function getFormByAssignment(IRoutineAssignment $assignment) : UIForm
    {
        $routine_id = $assignment->getRoutineId();
        $ref_id = $assignment->getRefId();

        if ((null !== $routine_id && null !== $ref_id) ||
            (null === $routine_id && null === $ref_id)
        ) {
            return $this->getStandardAssignmentForm();
        }

        if (null !== $routine_id) {
            return $this->getObjectAssignmentForm();
        }

        if (null !== $ref_id) {
            return $this->getRoutineAssignmentForm();
        }

        throw new LogicException("The given assignment is in an unknown state.");
    }

    /**
     * Returns the assignment form for existing assignments.
     *
     * @return UIForm
     */
    public function getStandardAssignmentForm() : UIForm
    {
        return $this->form_builder
            ->addStandardRoutineInput()
            ->addStandardObjectInput()
            ->getForm()
        ;
    }

    /**
     * Returns the assignment form for assigning object(s) to a routine.
     *
     * @return UIForm
     */
    public function getObjectAssignmentForm() : UIForm
    {
        return $this->form_builder
            ->addObjectAssignmentInput()
            ->addStandardRoutineInput()
            ->getForm()
        ;
    }

    /**
     * Returns the assignment form for assigning routine(s) to an object
     *
     * @return UIForm
     */
    public function getRoutineAssignmentForm() : UIForm
    {
        return $this->form_builder
            ->addRoutineAssignmentInput()
            ->addStandardObjectInput()
            ->getForm()
        ;
    }
}