<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form;

use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ilObject2;
use ilPlugin;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class FormBuilder implements IFormBuilder
{
    private const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';

    /**
     * @var InputFactory
     */
    protected $input_factory;

    /**
     * @var FormFactory
     */
    protected $form_factory;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var ilPlugin
     */
    protected $plugin;

    /**
     * @param ilPlugin     $plugin
     * @param InputFactory $input_factory
     * @param FormFactory  $form_factory
     * @param Refinery     $refinery
     */
    public function __construct(
        InputFactory $input_factory,
        FormFactory $form_factory,
        Refinery $refinery,
        ilPlugin $plugin
    ) {
        $this->input_factory = $input_factory;
        $this->form_factory = $form_factory;
        $this->refinery = $refinery;
        $this->plugin = $plugin;
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
            $this->plugin->txt(self::MSG_INVALID_REF_ID)
        );
    }

    /**
     * @param string $lang_var
     * @return string
     */
    protected function translate(string $lang_var) : string
    {
        return $this->plugin->txt($lang_var);
    }
}