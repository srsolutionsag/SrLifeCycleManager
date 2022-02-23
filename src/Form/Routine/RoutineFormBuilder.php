<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use DateTime;

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
    public const INPUT_EXECUTION_DATES = 'input_name_execution_dates';

    protected const INPUT_INFO_EXECUTION_DATES = 'input_name_execution_dates_info';
    protected const MSG_INVALID_EXECUTION_DATE = 'msg_invalid_execution_dates';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param FormFactory  $form_factory
     * @param InputFactory $input_factory
     * @param Refinery     $refinery
     * @param ITranslator  $translator
     * @param string       $form_action
     * @param IRoutine     $routine
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action,
        IRoutine $routine
    ) {
        parent::__construct($form_factory, $input_factory, $refinery, $translator, $form_action);

        $this->routine = $routine;
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

        $inputs[self::INPUT_EXECUTION_DATES] = $this->input_factory
            ->tag(
                $this->translate(self::INPUT_EXECUTION_DATES),
                [], // all values are user generated, no predefined values necessary.
                $this->translate(self::INPUT_INFO_EXECUTION_DATES)
            )
            ->withRequired(true)
            ->withAdditionalTransformation($this->getIlias6TagInputTransformation())
            ->withAdditionalTransformation($this->getExecutionDatesValidationConstraint())
            ->withValue($this->routine->getExecutionDates())
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

    /**
     * Returns a transformation for ILIAS 6, where the first array entry
     * of the tag-input value is removed.
     *
     * By default, the input will always submit an empty string as first
     * entry, which leads to unexpected validations.
     *
     * @return Transformation
     */
    protected function getIlias6TagInputTransformation() : Transformation
    {
        $is_ilias_6 = (bool) version_compare(ILIAS_VERSION_NUMERIC, "7.0", '<=');
        return $this->refinery->custom()->transformation(
            static function (array $values) use ($is_ilias_6) : ?array {
                if ($is_ilias_6 && empty($values[0])) {
                    unset($values[0]);
                    return array_values($values);
                }

                return $values;
            }
        );
    }

    /**
     * Returns a validation constraint the checks if a tag-input's values
     * match @see IRoutine::EXECUTION_DATES_FORMAT.
     *
     * @return Constraint
     */
    protected function getExecutionDatesValidationConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (array $values) : ?array {
                // check if each submitted date matches the routine's format.
                foreach ($values as $date) {
                    if (false === DateTime::createFromFormat(IRoutine::EXECUTION_DATES_FORMAT, $date)) {
                        return null;
                    }
                }

                return $values;
            },
            $this->translate(self::MSG_INVALID_EXECUTION_DATE)
        );
    }
}