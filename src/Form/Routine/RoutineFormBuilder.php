<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use ILIAS\UI\Component\Input\Container\Form\Factory;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineFormBuilder extends AbstractFormBuilder
{
    // RoutineFormBuilder inputs:
    public const INPUT_ELONGATION = 'input_name_routine_elongation';
    public const INPUT_ELONGATION_COOLDOWN = 'input_name_routine_elongation_cooldown';
    public const INPUT_ELONGATION_POSSIBLE = 'input_name_routine_elongation_possible';
    public const INPUT_HAS_OPT_OUT = 'input_name_routine_has_opt_out';
    public const INPUT_ROUTINE_TYPE = 'input_name_routine_type';
    public const INPUT_TITLE = 'input_name_routine_title';

    protected IRoutine $routine;

    /**
     * @param ITranslator   $translator
     * @param mixed $forms
     * @param mixed $fields
     * @param mixed $refinery
     * @param string        $form_action
     * @param IRoutine|null $routine
     */
    public function __construct(
        ITranslator $translator,
        Factory $forms,
        \ILIAS\UI\Component\Input\Field\Factory $fields,
        \ILIAS\Refinery\Factory $refinery,
        IRoutine $routine,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    public function getForm(): Form
    {
        $inputs[self::INPUT_TITLE] = $this->fields
            ->text($this->translator->txt(self::INPUT_TITLE))
            ->withRequired(true)
            ->withAdditionalTransformation($this->refinery->string()->hasMinLength(1))
            ->withValue($this->routine->getTitle());

        $inputs[self::INPUT_ROUTINE_TYPE] = $this->fields
            ->select($this->translator->txt(self::INPUT_ROUTINE_TYPE), [
                IRoutine::ROUTINE_TYPE_COURSE => $this->translator->txt(IRoutine::ROUTINE_TYPE_COURSE),
                IRoutine::ROUTINE_TYPE_GROUP => $this->translator->txt(IRoutine::ROUTINE_TYPE_GROUP),
                IRoutine::ROUTINE_TYPE_SURVEY => $this->translator->txt(IRoutine::ROUTINE_TYPE_SURVEY),
            ])
            ->withRequired(true)
            ->withValue($this->routine->getRoutineType())
            // the routine type cannot be changed after creation, because
            // rules are added considering by this attribute.
            ->withDisabled(null !== $this->routine->getRoutineId());

        $inputs[self::INPUT_HAS_OPT_OUT] = $this->fields
            ->checkbox($this->translator->txt(self::INPUT_HAS_OPT_OUT))
            ->withValue($this->routine->hasOptOut());

        $inputs[self::INPUT_ELONGATION_POSSIBLE] = $this->fields
            ->optionalGroup(
                [
                    self::INPUT_ELONGATION => $this->fields
                        ->numeric($this->translator->txt(self::INPUT_ELONGATION))
                        ->withRequired(true)
                        ->withValue($this->routine->getElongation())
                        ->withAdditionalTransformation(
                            $this->getAboveMinimumIntegerValidationConstraint(1)
                        )
                    ,
                    self::INPUT_ELONGATION_COOLDOWN => $this->fields
                        ->numeric(
                            $this->translator->txt(self::INPUT_ELONGATION_COOLDOWN),
                            $this->translator->txt(self::INPUT_ELONGATION_COOLDOWN . '_info')
                        )
                        ->withRequired(true)
                        ->withValue($this->routine->getElongationCooldown())
                        ->withAdditionalTransformation(
                            $this->getAboveMinimumIntegerValidationConstraint(0)
                        )
                ],
                $this->translator->txt(self::INPUT_ELONGATION_POSSIBLE)
            );

        // if the routine doesn't support elongations and by default,
        // set the display value of INPUT_ELONGATION_POSSIBLE to null
        // in order to uncheck the optional-group.
        if (null === $this->routine->getElongation()) {
            $inputs[self::INPUT_ELONGATION_POSSIBLE] = $inputs[self::INPUT_ELONGATION_POSSIBLE]->withValue(null);
        }

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }
}
