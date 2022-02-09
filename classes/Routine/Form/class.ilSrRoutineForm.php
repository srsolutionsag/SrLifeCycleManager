<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory;

/**
 * Class ilSrRoutineForm
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ilSrRoutineForm extends ilSrAbstractMainForm
{
    /**
     * ilSrRoutineForm form input names.
     */
    private const INPUT_REF_ID              = 'input_name_routine_ref_id';
    private const INPUT_NAME                = 'input_name_routine_name';
    private const INPUT_ACTIVE              = 'input_name_routine_active';
    private const INPUT_OPT_OUT             = 'input_name_routine_opt_out';
    private const INPUT_ELONGATION          = 'input_name_routine_elongation';
    private const INPUT_ELONGATION_POSSIBLE = 'input_name_routine_elongation_possible';

    /**
     * ilSrRoutineForm lang-vars.
     */
    private const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';

    /**
     * @var int
     */
    private $origin_type;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @var IRoutine
     */
    private $routine;

    /**
     * @var int|null
     */
    private $scope;

    /**
     * ilSrRoutineForm constructor
     *
     * @param UIServices                     $ui
     * @param ilCtrl                         $ctrl
     * @param Factory                        $refinery
     * @param ilSrLifeCycleManagerPlugin     $plugin
     * @param ilSrLifeCycleManagerRepository $repository
     * @param int                            $origin_type
     * @param int                            $owner_id
     * @param IRoutine|null                  $routine
     * @param int|null                       $scope
     */
    public function __construct(
        UIServices $ui,
        ilCtrl $ctrl,
        Factory $refinery,
        ilSrLifeCycleManagerPlugin $plugin,
        ilSrLifeCycleManagerRepository $repository,
        int $origin_type,
        int $owner_id,
        IRoutine $routine = null,
        int $scope = null
    ) {
        // dependencies MUST be added before the parent
        // constructor is called, as they are already by it.
        $this->origin_type = $origin_type;
        $this->owner_id    = $owner_id;
        $this->routine     = $routine;
        $this->scope       = $scope;

        parent::__construct($ui, $ctrl, $refinery, $plugin, $repository);
    }

    /**
     * @inheritDoc
     */
    protected function getFormAction() : string
    {
        // if the form has been initialized with a routine,
        // the id must be set as a GET parameter before
        // generating the form-action.
        if (null !== $this->routine) {
            $this->ctrl->setParameterByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::QUERY_PARAM_ROUTINE_ID,
                $this->routine->getId()
            );
        }

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
            // note that provided scope is only used if no routine was provided,
            // as we don't want to override the already stored value.
            ->withValue((null !== $this->routine) ? (string) $this->routine->getRefId() : (string) $this->scope)
            // if a scope was provided the value of this input should not
            // be changeable by the user.
            ->withDisabled(null !== $this->scope)
            ->withAdditionalTransformation($this->refinery->numeric()->isNumeric())
            ->withAdditionalTransformation($this->refinery->custom()->transformation(
                $this->getTypeCastClosure(self::TYPE_CAST_INT)
            ))
            ->withAdditionalTransformation($this->refinery->custom()->constraint(
                $this->getRefIdValidationClosure(),
                $this->plugin->txt(self::MSG_INVALID_REF_ID)
            ))
        ;

        $inputs[self::INPUT_NAME] = $this->inputs
            ->text($this->plugin->txt(self::INPUT_NAME))
            ->withRequired(true)
            ->withValue((null !== $this->routine) ? $this->routine->getName() : '')
            ->withAdditionalTransformation($this->refinery->string()->hasMinLength(1))
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
                        ->withValue((null !== $this->routine) ? (string) $this->routine->getElongationDays() : '')
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
        if (null === $this->routine ||  1 > $this->routine->getElongationDays()) {
            $inputs[self::INPUT_ELONGATION_POSSIBLE] = $inputs[self::INPUT_ELONGATION_POSSIBLE]->withValue(null);
        }

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // ensures that at least the routine's ref-id
        // and name must are submitted.
        return null !== $form_data[self::INPUT_REF_ID] && null !== $form_data[self::INPUT_NAME];
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        if (null === $this->routine) {
            $this->routine = $this->repository->routine()->getEmpty(
                $this->origin_type,
                $this->owner_id
            );
        }

        $this->routine
            ->setRefId($form_data[self::INPUT_REF_ID])
            ->setName($form_data[self::INPUT_NAME])
            ->setActive($form_data[self::INPUT_ACTIVE])
            ->setOptOutPossible($form_data[self::INPUT_OPT_OUT])
        ;

        // if elongation is possible, update the elongation
        // in days attribute. If it's been disabled set the
        // value to null instead.
        if (!empty($form_data[self::INPUT_ELONGATION_POSSIBLE])) {
            $this->routine->setElongationDays($form_data[self::INPUT_ELONGATION_POSSIBLE][self::INPUT_ELONGATION]);
        } else {
            $this->routine->setElongationDays(null);
        }

        $this->repository->routine()->store($this->routine);
    }
}