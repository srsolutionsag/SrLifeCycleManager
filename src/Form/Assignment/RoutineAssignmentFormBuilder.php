<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormBuilder extends AbstractFormBuilder
{
    // RoutineAssignmentFormBuilder inputs:
    public const INPUT_ROUTINE = 'input_name_routine_assignment_routine';
    public const INPUT_REF_ID = 'input_name_routine_assignment_ref_id';
    public const INPUT_IS_RECURSIVE = 'input_name_routine_assignment_recursive';
    public const INPUT_IS_ACTIVE = 'input_name_routine_assignment_active';

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @var IRoutineRepository
     */
    protected $repository;

    /**
     * @param ITranslator        $translator
     * @param FormFactory        $forms
     * @param FieldFactory       $fields
     * @param Refinery           $refinery
     * @param IRoutineAssignment $assignment
     * @param IRoutineRepository $repository
     * @param string             $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutineAssignment $assignment,
        IRoutineRepository $repository,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->assignment = $assignment;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        $inputs[self::INPUT_ROUTINE] = $this->fields->select(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions()
        );

        $inputs[self::INPUT_REF_ID] = $this->fields->numeric(
            $this->translator->txt(self::INPUT_REF_ID)
        )->withAdditionalTransformation(
            $this->getRefIdValidationConstraint()
        );

        $inputs[self::INPUT_IS_RECURSIVE] = $this->fields->checkbox(
            $this->translator->txt(self::INPUT_IS_RECURSIVE)
        );

        $inputs[self::INPUT_IS_ACTIVE] = $this->fields->checkbox(
            $this->translator->txt(self::INPUT_IS_ACTIVE)
        );

        if (null !== $this->assignment) {
            $inputs[self::INPUT_ROUTINE] = $inputs[self::INPUT_ROUTINE]->withValue($this->assignment->getRoutine()->getRoutineId());
            $inputs[self::INPUT_REF_ID] = $inputs[self::INPUT_REF_ID]->withValue($this->assignment->getRefId());
            $inputs[self::INPUT_IS_RECURSIVE] = $inputs[self::INPUT_IS_RECURSIVE]->withValue($this->assignment->isRecursive());
            $inputs[self::INPUT_IS_ACTIVE] = $inputs[self::INPUT_IS_ACTIVE]->withValue($this->assignment->isActive());
        }

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getRoutineOptions() : array
    {
        return [];
    }
}