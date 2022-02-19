<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Config;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfigFormBuilder extends AbstractFormBuilder
{
    /**
     * @var IConfig[]
     */
    protected $config;

    /**
     * @var array
     */
    protected $global_roles;

    /**
     * @var bool
     */
    protected $bin_active;

    /**
     * @param FormFactory  $form_factory
     * @param InputFactory $input_factory
     * @param Refinery     $refinery
     * @param ITranslator  $translator
     * @param string       $form_action
     * @param IConfig[]    $config
     * @param array        $global_roles
     * @param bool         $bin_active
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action,
        array $config,
        array $global_roles,
        bool $bin_active
    ) {
        parent::__construct($form_factory, $input_factory, $refinery, $translator, $form_action);

        $this->config = $config;
        $this->global_roles = $global_roles;
        $this->bin_active = $bin_active;
    }

    /**
     * @inheritDoc
     */
    protected function getInputs() : array
    {
        $inputs[IConfig::CNF_GLOBAL_ROLES] = $this->input_factory
            ->multiSelect($this->translate(IConfig::CNF_GLOBAL_ROLES), $this->global_roles)
            ->withValue((isset($this->config[IConfig::CNF_GLOBAL_ROLES])) ?
                $this->config[IConfig::CNF_GLOBAL_ROLES]->getValue() : []
            )
        ;

        // add move-to-bin input only if ILIAS trash is enabled.
        if ($this->bin_active) {
            $inputs[IConfig::CNF_MOVE_TO_BIN] = $this->input_factory
                ->checkbox($this->translate(IConfig::CNF_MOVE_TO_BIN))
                ->withValue(
                    isset($this->config[IConfig::CNF_MOVE_TO_BIN]) &&
                    $this->config[IConfig::CNF_MOVE_TO_BIN]->getValue()
                )
            ;
        }

        $inputs[IConfig::CNF_SHOW_ROUTINES] = $this->input_factory
            ->checkbox($this->translate(IConfig::CNF_SHOW_ROUTINES))
            ->withValue(
                isset($this->config[IConfig::CNF_SHOW_ROUTINES]) &&
                $this->config[IConfig::CNF_SHOW_ROUTINES]->getValue()
            )
        ;

        $inputs[IConfig::CNF_CREATE_ROUTINES] = $this->input_factory
            ->checkbox($this->translate(IConfig::CNF_CREATE_ROUTINES))
            ->withValue(
                isset($this->config[IConfig::CNF_CREATE_ROUTINES]) &&
                $this->config[IConfig::CNF_CREATE_ROUTINES]->getValue()
            )
        ;

        return $inputs;
    }
}