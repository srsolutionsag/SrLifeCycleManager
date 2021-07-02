<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;

/**
 * Class ilSrRoutineForm
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineForm extends ilSrAbstractMainForm
{
    /**
     * ilSrRoutineForm form input names.
     */
    private const INPUT_REF_ID              = 'input_name_routine_ref_id';
    private const INPUT_ACTIVE              = 'input_name_routine_active';
    private const INPUT_OPT_OUT             = 'input_name_routine_opt_out';
    private const INPUT_ELONGATION          = 'input_name_routine_elongation';
    private const INPUT_ELONGATION_POSSIBLE = 'routine_input_elongation_possible';

    /**
     * ilSrRoutineForm lang-vars.
     */
    private const MSG_INVALID_REF_ID    = 'msg_invalid_ref_id';

    /**
     * @var IRoutine
     */
    private $routine;

    /**
     * @var int
     */
    private $origin_type;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * ilSrRoutineForm constructor.
     *
     * @param int           $origin_type
     * @param int           $owner_id
     * @param IRoutine|null $routine
     */
    public function __construct(int $origin_type, int $owner_id, IRoutine $routine = null)
    {
        // dependencies MUST be added before the parent
        // constructor is called, as they are already by it.
        $this->origin_type = $origin_type;
        $this->owner_id    = $owner_id;
        $this->routine     = $routine;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::CMD_ROUTINE_SAVE
        );
    }

    /**
     * @inheritDoc
     */
    protected function getFormInputs() : array
    {
        $inputs = [];

        $inputs[self::INPUT_REF_ID] = $this->inputs
            ->text($this->plugin->txt(self::INPUT_REF_ID))
            ->withRequired(true)
            ->withValue((null !== $this->routine) ? $this->routine->getRefId() : '')
            ->withAdditionalTransformation($this->refinery->numeric()->isNumeric())
            ->withAdditionalTransformation($this->refinery->custom()->transformation(
                $this->getTypeCastClosure(self::TYPE_CAST_INT)
            ))
            ->withAdditionalTransformation($this->refinery->custom()->constraint(
                $this->getRefIdValidationClosure(),
                $this->plugin->txt(self::MSG_INVALID_REF_ID)
            ))
        ;

        $inputs[self::INPUT_ACTIVE] = $this->inputs
            ->checkbox($this->plugin->txt(self::INPUT_ACTIVE))
            ->withValue((null !== $this->routine && $this->routine->isActive()))
        ;

        $inputs[self::INPUT_OPT_OUT] = $this->inputs
            ->checkbox($this->plugin->txt(self::INPUT_OPT_OUT))
            ->withValue((null !== $this->routine && $this->routine->isOptOutPossible()))
        ;

        $inputs[self::INPUT_ELONGATION_POSSIBLE] = $this->inputs
            ->optionalGroup(
                [
                    self::INPUT_ELONGATION => $this->inputs
                        ->text($this->plugin->txt(self::INPUT_ELONGATION))
                        ->withValue((null !== $this->routine) ? $this->routine->getRefId() : '')
                        ->withAdditionalTransformation($this->refinery->numeric()->isNumeric())
                        ->withAdditionalTransformation($this->refinery->custom()->transformation(
                            $this->getTypeCastClosure(self::TYPE_CAST_INT)
                        ))
                    ,
                ],
                $this->plugin->txt(self::INPUT_ELONGATION_POSSIBLE)
            )
        ;

        // if the routine doesn't support elongations and by default,
        // set the display value of INPUT_ELONGATION_POSSIBLE to null
        // in order to uncheck the optional-group.
        if (null === $this->routine || !$this->routine->isElongationPossible()) {
            $inputs[self::INPUT_ELONGATION_POSSIBLE] = $inputs[self::INPUT_ELONGATION_POSSIBLE]->withValue(null);
        }

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // the form data is valid when a valid scope has been
        // provided. Since this form-input has a validation
        // closure appended the null-check is sufficient.
        return (isset($form_data[self::INPUT_REF_ID]) && null !== $form_data[self::INPUT_REF_ID]);
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        if (null === $this->routine) {
            $this->routine = $this->repository->routine()->getEmptyDTO(
                $this->origin_type,
                $this->owner_id
            );
        }

        $this->routine
            ->setRefId($form_data[self::INPUT_REF_ID])
            ->setActive($form_data[self::INPUT_ACTIVE])
            ->setOptOutPossible($form_data[self::INPUT_OPT_OUT])
            // submitted optional-group is either null or an array
            // of inputs, hence a (not) empty-check is used.
            ->setElongationPossible(!empty($form_data[self::INPUT_ELONGATION_POSSIBLE]))
        ;

        if ($this->routine->isElongationPossible()) {
            $this->routine->setElongationDays($form_data[self::INPUT_ELONGATION_POSSIBLE][self::INPUT_ELONGATION]);
        }

        $this->repository->routine()->store($this->routine);
    }
}