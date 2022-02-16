<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form\Config;

use srag\Plugins\SrLifeCycleManager\Builder\Form\FormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ilSrConfig;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfigFormBuilder extends FormBuilder
{
    /**
     * @var bool
     */
    protected $is_bin_active = false;

    /**
     * @var string[]
     */
    protected $global_roles = [];

    /**
     * @param bool $is_bin_active
     * @return $this
     */
    public function withBinActive(bool $is_bin_active) : self
    {
        $this->is_bin_active = $is_bin_active;
        return $this;
    }

    /**
     * @param string[] $global_roles
     * @return $this
     */
    public function withGlobalRoles(array $global_roles) : self
    {
        $this->global_roles = $global_roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getForm(string $form_action) : Form
    {
        $inputs = [];

        $inputs[ilSrConfig::CNF_GLOBAL_ROLES] = $this->input_factory
            ->multiSelect($this->translate(ilSrConfig::CNF_GLOBAL_ROLES), $this->global_roles)
            ->withValue((isset($this->config[ilSrConfig::CNF_GLOBAL_ROLES])) ?
                $this->config[ilSrConfig::CNF_GLOBAL_ROLES]->getValue() : []
            )
        ;

        // add move-to-bin input only if ILIAS trash is enabled.
        if ($this->is_bin_active) {
            $inputs[ilSrConfig::CNF_MOVE_TO_BIN] = $this->input_factory
                ->checkbox($this->translate(ilSrConfig::CNF_MOVE_TO_BIN))
                ->withValue(
                    isset($this->config[ilSrConfig::CNF_MOVE_TO_BIN]) &&
                    $this->config[ilSrConfig::CNF_MOVE_TO_BIN]->getValue()
                )
            ;
        }

        $inputs[ilSrConfig::CNF_SHOW_ROUTINES] = $this->input_factory
            ->checkbox($this->translate(ilSrConfig::CNF_SHOW_ROUTINES))
            ->withValue(
                isset($this->config[ilSrConfig::CNF_SHOW_ROUTINES]) &&
                $this->config[ilSrConfig::CNF_SHOW_ROUTINES]->getValue()
            )
        ;

        $inputs[ilSrConfig::CNF_CREATE_ROUTINES] = $this->input_factory
            ->checkbox($this->translate(ilSrConfig::CNF_CREATE_ROUTINES))
            ->withValue(
                isset($this->config[ilSrConfig::CNF_CREATE_ROUTINES]) &&
                $this->config[ilSrConfig::CNF_CREATE_ROUTINES]->getValue()
            )
        ;

        return $this->form_factory->standard(
            $form_action,
            $inputs
        );
    }
}