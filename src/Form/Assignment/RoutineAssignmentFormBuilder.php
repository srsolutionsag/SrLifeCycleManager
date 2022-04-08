<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Input;
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
     * @var IRoutine[]
     */
    protected $possible_routines;

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @var Input[]
     */
    protected $inputs;

    /**
     * @var string
     */
    protected $ajax_action;

    /**
     * @param ITranslator        $translator
     * @param FormFactory        $forms
     * @param FieldFactory       $fields
     * @param Refinery           $refinery
     * @param IRoutineAssignment $assignment
     * @param IRoutine[]         $possible_routines
     * @param string             $form_action
     * @param string             $ajax_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutineAssignment $assignment,
        array $possible_routines,
        string $form_action,
        string $ajax_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->possible_routines = $possible_routines;
        $this->ajax_action = $ajax_action;
        $this->assignment = $assignment;
        $this->inputs = [];
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        // add default is_recursive input (looks always the same).
        $this->inputs[self::INPUT_IS_RECURSIVE] = $this->fields->checkbox(
            $this->translator->txt(self::INPUT_IS_RECURSIVE)
        )->withValue(
            $this->assignment->isRecursive()
        );

        // add default is_active input (looks always the same).
        $this->inputs[self::INPUT_IS_ACTIVE] = $this->fields->checkbox(
            $this->translator->txt(self::INPUT_IS_ACTIVE)
        )->withValue(
            $this->assignment->isActive()
        );

        return $this->forms->standard(
            $this->form_action,
            $this->inputs
        );
    }

    /**
     * Adds an input that can assign multiple objects.
     *
     * @return self
     */
    public function addObjectAssignmentInput() : self
    {
        $this->inputs[self::INPUT_REF_ID] = $this->fields->tag(
            $this->translator->txt(self::INPUT_REF_ID),
            [] // all values are user generated.
        )->withAdditionalOnLoadCode(
            $this->getTagInputAutoCompleteBinder($this->ajax_action)
        )->withAdditionalTransformation(
            $this->getRefIdArrayValidationConstraint(true)
        )->withRequired(true);

        return $this;
    }

    /**
     * Adds an input that can assign ONE object.
     *
     * @return self
     */
    public function addStandardObjectInput() : self
    {
        $this->inputs[self::INPUT_REF_ID] = $this->fields->numeric(
            $this->translator->txt(self::INPUT_REF_ID)
        )->withAdditionalTransformation(
            $this->getRefIdValidationConstraint()
        )->withRequired(true);

        if (null !== ($ref_id = $this->assignment->getRefId())) {
            $this->inputs[self::INPUT_REF_ID] = $this->inputs[self::INPUT_REF_ID]
                ->withValue($ref_id)
                ->withDisabled(true)
            ;
        }

        return $this;
    }

    /**
     * Adds an input that can assign multiple routines.
     *
     * @return self
     */
    public function addRoutineAssignmentInput() : self
    {
        $this->inputs[self::INPUT_ROUTINE] = $this->fields->multiSelect(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions()
        )->withRequired(true);

        return $this;
    }

    /**
     * Adds an input that can assign ONE routine.
     *
     * @return self
     */
    public function addStandardRoutineInput() : self
    {
        $this->inputs[self::INPUT_ROUTINE] = $this->fields->select(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions()
        )->withValue(
            $this->assignment->getRoutineId()
        )->withRequired(true);

        if (null !== ($routine_id = $this->assignment->getRoutineId())) {
            $this->inputs[self::INPUT_ROUTINE] = $this->inputs[self::INPUT_ROUTINE]
                ->withValue($routine_id)
                ->withDisabled(true)
            ;
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    protected function getRoutineOptions() : array
    {
        $options = [];
        foreach ($this->possible_routines as $routine) {
            $options[$routine->getRoutineId()] = $routine->getTitle();
        }

        return $options;
    }
}