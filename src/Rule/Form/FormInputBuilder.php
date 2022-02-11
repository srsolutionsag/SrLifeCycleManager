<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form;

use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;
use ilPlugin;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class FormInputBuilder implements IFormInputBuilder
{
    /**
     * @var InputFactory
     */
    protected $input_factory;

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
     * @param Refinery     $refinery
     */
    public function __construct(
        InputFactory $input_factory,
        Refinery $refinery,
        ilPlugin $plugin
    ) {
        $this->input_factory = $input_factory;
        $this->refinery = $refinery;
        $this->plugin = $plugin;
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