<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use ILIAS\UI\Component\Input\Container\Form\Factory;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Field\Input;

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

    protected IRoutineAssignment $assignment;

    /**
     * @var IRoutine[]
     */
    protected array $all_routines;

    /**
     * @param IRoutineAssignment $assignment
     * @param IRoutine[]         $all_routines
     * @inheritDoc
     * @param mixed $forms
     * @param mixed $fields
     * @param mixed $refinery
     */
    public function __construct(
        ITranslator $translator,
        Factory $forms,
        \ILIAS\UI\Component\Input\Field\Factory $fields,
        \ILIAS\Refinery\Factory $refinery,
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
    public function getForm(): Form
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
    protected function getImmutableRoutineInput(): Input
    {
        return $this->fields->select(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions($this->all_routines)
        )->withValue(
            // make sure to cast value to string, because required select inputs
            // expect it to be, see https://jira.sr.solutions/browse/PLSRLCM-59
            (string) $this->assignment->getRoutineId()
        )->withRequired(
            true
        )->withDisabled(
            true
        );
    }

    /**
     * @return Input
     */
    protected function getImmutableObjectInput(): Input
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
    protected function getRoutineOptions(array $routines): array
    {
        $options = [];
        foreach ($routines as $routine) {
            // make sure to cast keys to strings, because required select inputs
            // expect them to be, see https://jira.sr.solutions/browse/PLSRLCM-59
            $options[(string) $routine->getRoutineId()] = $routine->getTitle();
        }

        return $options;
    }

    /**
     * @return Input
     */
    abstract protected function getRoutineInput(): Input;

    /**
     * @return Input
     */
    abstract protected function getObjectInput(): Input;
}
