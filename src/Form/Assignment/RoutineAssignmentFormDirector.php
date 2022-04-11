<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentIntention;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormDirector
{
    /**
     * @var IRoutine[]
     */
    protected $unassigned_routines;

    /**
     * @var RoutineAssignmentFormBuilder
     */
    protected $form_builder;

    /**
     * @param RoutineAssignmentFormBuilder $form_builder
     * @param array                        $unassigned_routines
     */
    public function __construct(RoutineAssignmentFormBuilder $form_builder, array $unassigned_routines)
    {
        $this->unassigned_routines = $unassigned_routines;
        $this->form_builder = $form_builder;
    }

    /**
     * Returns the corresponding assignment form for the given intention.
     *
     * @param IRoutineAssignmentIntention $intention
     * @return UIForm
     */
    public function getFormByIntention(IRoutineAssignmentIntention $intention) : UIForm
    {
        switch ($intention->getIntention()) {
            case IRoutineAssignmentIntention::EDIT_ASSIGNMENT:
                return $this->getStandardAssignmentForm();

            case IRoutineAssignmentIntention::ROUTINE_ASSIGNMENT:
                return $this->getRoutineAssignmentForm();

            case IRoutineAssignmentIntention::OBJECT_ASSIGNMENT:
                return $this->getObjectAssignmentForm();

            default:
                throw new LogicException("The given assignment is in an unknown state.");
        }
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