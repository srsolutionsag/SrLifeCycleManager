<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Builder\Form\FormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineFormBuilder extends FormBuilder
{
    public const INPUT_REF_ID              = 'input_name_routine_ref_id';
    public const INPUT_NAME                = 'input_name_routine_name';
    public const INPUT_ACTIVE              = 'input_name_routine_active';
    public const INPUT_OPT_OUT             = 'input_name_routine_opt_out';
    public const INPUT_ELONGATION          = 'input_name_routine_elongation';
    public const INPUT_ELONGATION_POSSIBLE = 'input_name_routine_elongation_possible';
    
    /**
     * @var IRoutine|null
     */
    protected $routine = null;

    /**
     * @var int|null
     */
    protected $scope = null;

    /**
     * @param IRoutine|null $routine
     * @return $this
     */
    public function withRoutine(?IRoutine $routine) : self
    {
        $this->routine = $routine;
        return $this;
    }

    /**
     * @param int|null $scope
     * @return $this
     */
    public function withScope(?int $scope) : self
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getForm(string $form_action) : Form
    {
        $inputs = [];

        $inputs[self::INPUT_REF_ID] = $this->input_factory
            ->numeric($this->translate(self::INPUT_REF_ID))
            ->withRequired(true)
            // note that provided scope is only used if no routine was provided,
            // as we don't want to override the already stored value.
            ->withValue((null !== $this->routine) ? (string) $this->routine->getRefId() : (string) $this->scope)
            // if a scope was provided the value of this input should not
            // be changeable by the user.
            ->withDisabled(null !== $this->scope)
            ->withAdditionalTransformation($this->getRefIdValidationConstraint())
        ;

        $inputs[self::INPUT_NAME] = $this->input_factory
            ->text($this->translate(self::INPUT_NAME))
            ->withRequired(true)
            ->withValue((null !== $this->routine) ? $this->routine->getName() : '')
            ->withAdditionalTransformation($this->refinery->string()->hasMinLength(1))
        ;

        $inputs[self::INPUT_ACTIVE] = $this->input_factory
            ->checkbox($this->translate(self::INPUT_ACTIVE))
            ->withValue((null !== $this->routine && $this->routine->isActive()))
        ;

        $inputs[self::INPUT_OPT_OUT] = $this->input_factory
            ->checkbox($this->translate(self::INPUT_OPT_OUT))
            ->withValue((null !== $this->routine && $this->routine->isOptOutPossible()))
        ;

        $inputs[self::INPUT_ELONGATION_POSSIBLE] = $this->input_factory
            ->optionalGroup(
                [
                    self::INPUT_ELONGATION => $this->input_factory
                        ->numeric($this->translate(self::INPUT_ELONGATION))
                        ->withValue((null !== $this->routine) ? (string) $this->routine->getElongationDays() : '')
                    ,
                ],
                $this->translate(self::INPUT_ELONGATION_POSSIBLE)
            )
        ;

        // if the routine doesn't support elongations and by default,
        // set the display value of INPUT_ELONGATION_POSSIBLE to null
        // in order to uncheck the optional-group.
        if (null === $this->routine ||  1 > $this->routine->getElongationDays()) {
            $inputs[self::INPUT_ELONGATION_POSSIBLE]->withValue(null);
        }

        return $this->form_factory->standard(
            $form_action,
            $inputs
        );
    }
}