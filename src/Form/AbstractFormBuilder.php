<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Custom\Constraint;
use ilObject2;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormBuilder implements IFormBuilder
{
    private const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';

    /**
     * @var InputFactory
     */
    protected $input_factory;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var string|null
     */
    protected $form_action;

    /**
     * @var FormFactory
     */
    private $form_factory;

    /**
     * @param FormFactory  $form_factory
     * @param InputFactory $input_factory
     * @param Refinery     $refinery
     * @param ITranslator  $translator
     * @param string       $form_action
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action
    ) {
        $this->form_factory = $form_factory;
        $this->input_factory = $input_factory;
        $this->refinery = $refinery;
        $this->translator = $translator;
        $this->form_action = $form_action;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        return $this->form_factory->standard(
            $this->form_action,
            $this->getInputs()
        );
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
            $this->translate(self::MSG_INVALID_REF_ID)
        );
    }


    /**
     * @param string $lang_var
     * @return string
     */
    protected function translate(string $lang_var) : string
    {
        return $this->translator->txt($lang_var);
    }

    /**
     * @return Input[]
     */
    abstract protected function getInputs() : array;
}