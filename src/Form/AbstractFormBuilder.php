<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilObject2;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormBuilder implements IFormBuilder
{
    // AbstractFormBuilder language variables:
    private const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var FormFactory
     */
    protected $forms;

    /**
     * @var FieldFactory
     */
    protected $fields;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var string
     */
    protected $form_action;

    /**
     * @param ITranslator  $translator
     * @param FormFactory  $forms
     * @param FieldFactory $fields
     * @param Refinery     $refinery
     * @param string       $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        string $form_action
    ) {
        $this->translator = $translator;
        $this->forms = $forms;
        $this->fields = $fields;
        $this->refinery = $refinery;
        $this->form_action = $form_action;
    }

    /**
     * @return Constraint
     */
    protected function getRefIdValidationConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            static function(int $ref_id) : ?int {
                return (ilObject2::_exists($ref_id, true)) ? $ref_id : null;
            },
            $this->translator->txt(self::MSG_INVALID_REF_ID)
        );
    }

}