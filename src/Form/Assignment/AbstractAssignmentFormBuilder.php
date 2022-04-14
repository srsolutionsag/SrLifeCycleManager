<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractAssignmentFormBuilder extends AbstractFormBuilder
{
    // AbstractAssignmentFormBuilder inputs:
    public const INPUT_ROUTINE = 'input_name_routine_assignment_routine';
    public const INPUT_REF_ID = 'input_name_routine_assignment_ref_id';
    public const INPUT_IS_RECURSIVE = 'input_name_routine_assignment_recursive';
    public const INPUT_IS_ACTIVE = 'input_name_routine_assignment_active';

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @var IRoutine[]
     */
    protected $all_routines;

    /**
     * @param IRoutineAssignment $assignment
     * @param IRoutine[]         $all_routines
     * @inheritDoc
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutineAssignment $assignment,
        array $all_routines,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->assignment = $assignment;
        $this->all_routines = $all_routines;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        return $this->forms->standard(
            $this->form_action,
            [
                // use an immutable input with the value of the current assignment
                // if the value has been set.
                self::INPUT_ROUTINE => (null !== $this->assignment->getRoutineId()) ?
                    $this->getImmutableRoutineInput() : $this->getRoutineInput()
                ,

                // use an immutable input with the value of the current assignment
                // if the value has been set.
                self::INPUT_REF_ID => (null !== $this->assignment->getRefId()) ?
                    $this->getImmutableObjectInput() : $this->getObjectInput()
                ,

                self::INPUT_IS_RECURSIVE => $this->fields->checkbox(
                    $this->translator->txt(self::INPUT_IS_RECURSIVE)
                )->withValue(
                    $this->assignment->isRecursive()
                ),

                self::INPUT_IS_ACTIVE => $this->fields->checkbox(
                    $this->translator->txt(self::INPUT_IS_ACTIVE)
                )->withValue(
                    $this->assignment->isActive()
                ),
            ]
        );
    }

    /**
     * @return Input
     */
    protected function getImmutableRoutineInput() : Input
    {
        return $this->fields->select(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions($this->all_routines)
        )->withValue(
            $this->assignment->getRoutineId()
        )->withRequired(
            true
        )->withDisabled(
            true
        );
    }

    /**
     * @return Input
     */
    protected function getImmutableObjectInput() : Input
    {
        return $this->fields->numeric(
            $this->translator->txt(self::INPUT_REF_ID)
        )->withValue(
            $this->assignment->getRefId()
        )->withRequired(
            true
        )->withDisabled(
            true
        );
    }

    /**
     * Returns routine-id => routine-title pairs.
     * @param IRoutine[] $routines
     * @return array<int, string>
     */
    protected function getRoutineOptions(array $routines) : array
    {
        $options = [];
        foreach ($routines as $routine) {
            $options[$routine->getRoutineId()] = $routine->getTitle();
        }

        return $options;
    }

    /**
     * @return Input
     */
    abstract protected function getRoutineInput() : Input;

    /**
     * @return Input
     */
    abstract protected function getObjectInput() : Input;
}