<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineFormBuilder extends AbstractFormBuilder
{
    public const INPUT_REF_ID = 'input_name_routine_ref_id';
    public const INPUT_NAME = 'input_name_routine_name';
    public const INPUT_ACTIVE = 'input_name_routine_active';
    public const INPUT_OPT_OUT = 'input_name_routine_opt_out';
    public const INPUT_ELONGATION = 'input_name_routine_elongation';
    public const INPUT_ELONGATION_POSSIBLE = 'input_name_routine_elongation_possible';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var int|null
     */
    protected $scope;

    /**
     * @param FormFactory  $form_factory
     * @param InputFactory $input_factory
     * @param Refinery     $refinery
     * @param ITranslator  $translator
     * @param string       $form_action
     * @param IRoutine     $routine
     * @param int|null     $scope
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action,
        IRoutine $routine,
        int $scope = null
    ) {
        parent::__construct($form_factory, $input_factory, $refinery, $translator, $form_action);

        $this->routine = $routine;
        $this->scope = $scope;
    }

    /**
     * @return int|null
     */
    public function getScope() : ?int
    {
        return $this->scope;
    }

    /**
     * @return IRoutine
     */
    public function getRoutine() : IRoutine
    {
        return $this->routine;
    }

    /**
     * @inheritDoc
     */
    protected function getInputs() : array
    {
        $inputs[self::INPUT_REF_ID] = $this->input_factory
            ->numeric($this->translate(self::INPUT_REF_ID))
            ->withRequired(true)
            // if a scope was provided the value of this input should not
            // be changeable by the user.
            ->withDisabled(null !== $this->scope)
            ->withAdditionalTransformation($this->getRefIdValidationConstraint())
            ->withValue(
                (0 < $this->routine->getRefId()) ? $this->routine->getRefId() : null
            )
        ;

        $inputs[self::INPUT_NAME] = $this->input_factory
            ->text($this->translate(self::INPUT_NAME))
            ->withRequired(true)
            ->withAdditionalTransformation($this->refinery->string()->hasMinLength(1))
            ->withValue($this->routine->getName())
        ;

        $inputs[self::INPUT_ACTIVE] = $this->input_factory
            ->checkbox($this->translate(self::INPUT_ACTIVE))
            ->withValue($this->routine->isActive())
        ;

        $inputs[self::INPUT_OPT_OUT] = $this->input_factory
            ->checkbox($this->translate(self::INPUT_OPT_OUT))
            ->withValue($this->routine->isOptOutPossible())
        ;

        $inputs[self::INPUT_ELONGATION_POSSIBLE] = $this->input_factory
            ->optionalGroup(
                [
                    self::INPUT_ELONGATION => $this->input_factory
                        ->numeric($this->translate(self::INPUT_ELONGATION))
                        ->withValue($this->routine->getElongationDays())
                    ,
                ],
                $this->translate(self::INPUT_ELONGATION_POSSIBLE)
            )
        ;

        // if the routine doesn't support elongations and by default,
        // set the display value of INPUT_ELONGATION_POSSIBLE to null
        // in order to uncheck the optional-group.
        if (null === $this->routine->getElongationDays()) {
            $inputs[self::INPUT_ELONGATION_POSSIBLE] = $inputs[self::INPUT_ELONGATION_POSSIBLE]->withValue(null);
        }

        return $inputs;
    }
}