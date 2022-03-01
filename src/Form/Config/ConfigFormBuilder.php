<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Config;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfigFormBuilder extends AbstractFormBuilder
{
    /**
     * @var string[]
     */
    protected $global_roles;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @param ITranslator  $translator
     * @param FormFactory  $forms
     * @param FieldFactory $fields
     * @param Refinery     $refinery
     * @param IConfig      $config
     * @param string[]     $global_roles
     * @param string       $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IConfig $config,
        array $global_roles,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->global_roles = $global_roles;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        $inputs[IConfig::CNF_PRIVILEGED_ROLES] = $this->fields
            ->multiSelect($this->translator->txt(IConfig::CNF_PRIVILEGED_ROLES), $this->global_roles)
            ->withValue((!empty($this->config->getPrivilegedRoles())) ?
                $this->config->getPrivilegedRoles() : null
            )
        ;

        $inputs[IConfig::CNF_SHOW_ROUTINES] = $this->fields
            ->checkbox($this->translator->txt(IConfig::CNF_SHOW_ROUTINES))
            ->withValue($this->config->canToolShowRoutines())
        ;

        $inputs[IConfig::CNF_CREATE_ROUTINES] = $this->fields
            ->checkbox($this->translator->txt(IConfig::CNF_CREATE_ROUTINES))
            ->withValue($this->config->canToolCreateRoutines())
        ;

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }
}