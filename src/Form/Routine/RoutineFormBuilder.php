<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineFormBuilder extends AbstractFormBuilder
{
    // RoutineFormBuilder inputs:
    public const INPUT_ELONGATION = 'input_name_routine_elongation';
    public const INPUT_ELONGATION_POSSIBLE = 'input_name_routine_elongation_possible';
    public const INPUT_HAS_OPT_OUT = 'input_name_routine_has_opt_out';
    public const INPUT_IS_ACTIVE  = 'input_name_routine_is_active';
    public const INPUT_REF_ID = 'input_name_routine_ref_id';
    public const INPUT_ROUTINE_TYPE = 'input_name_routine_type';
    public const INPUT_TITLE = 'input_name_routine_title';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param ITranslator   $translator
     * @param FormFactory   $forms
     * @param FieldFactory  $fields
     * @param Refinery      $refinery
     * @param string        $form_action
     * @param IRoutine|null $routine
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutine $routine,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        $inputs[self::INPUT_REF_ID] = $this->fields
            ->numeric($this->translator->txt(self::INPUT_REF_ID))
            ->withRequired(true)
            ->withAdditionalTransformation($this->getRefIdValidationConstraint())
            ->withValue(
                (0 < $this->routine->getRefId()) ? $this->routine->getRefId() : null
            )
        ;

        $inputs[self::INPUT_TITLE] = $this->fields
            ->text($this->translator->txt(self::INPUT_TITLE))
            ->withRequired(true)
            ->withAdditionalTransformation($this->refinery->string()->hasMinLength(1))
            ->withValue($this->routine->getTitle())
        ;

        $inputs[self::INPUT_ROUTINE_TYPE] = $this->fields
            ->select($this->translator->txt(self::INPUT_ROUTINE_TYPE), [
                IRoutine::ROUTINE_TYPE_COURSE => $this->translator->txt(IRoutine::ROUTINE_TYPE_COURSE),
                IRoutine::ROUTINE_TYPE_GROUP  => $this->translator->txt(IRoutine::ROUTINE_TYPE_GROUP),
            ])
            ->withRequired(true)
            ->withValue($this->routine->getRoutineType())
            // the routine type cannot be changed after creation, because
            // rules are added considering by this attribute.
            ->withDisabled(null !== $this->routine->getRoutineId())
        ;

        $inputs[self::INPUT_IS_ACTIVE] = $this->fields
            ->checkbox($this->translator->txt(self::INPUT_IS_ACTIVE))
            ->withValue($this->routine->isActive())
        ;

        $inputs[self::INPUT_HAS_OPT_OUT] = $this->fields
            ->checkbox($this->translator->txt(self::INPUT_HAS_OPT_OUT))
            ->withValue($this->routine->hasOptOut())
        ;

        $inputs[self::INPUT_ELONGATION_POSSIBLE] = $this->fields
            ->optionalGroup(
                [
                    self::INPUT_ELONGATION => $this->fields
                        ->numeric($this->translator->txt(self::INPUT_ELONGATION))
                        ->withValue($this->routine->getElongation())
                    ,
                ],
                $this->translator->txt(self::INPUT_ELONGATION_POSSIBLE)
            )
        ;

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