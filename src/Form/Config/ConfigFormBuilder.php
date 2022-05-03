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
        $inputs[IConfig::CNF_ROLE_MANAGE_ROUTINES] = $this->fields
            ->multiSelect($this->translator->txt(IConfig::CNF_ROLE_MANAGE_ROUTINES), $this->global_roles)
            ->withValue((!empty($this->config->getManageRoutineRoles())) ?
                $this->config->getManageRoutineRoles() : null
            )
        ;

        $inputs[IConfig::CNF_ROLE_MANAGE_ASSIGNMENTS] = $this->fields
            ->multiSelect($this->translator->txt(IConfig::CNF_ROLE_MANAGE_ASSIGNMENTS), $this->global_roles)
            ->withValue((!empty($this->config->getManageAssignmentRoles())) ?
                $this->config->getManageAssignmentRoles() : null
            )
        ;

        $inputs[IConfig::CNF_CUSTOM_FROM_EMAIL] = $this->fields
            ->text($this->translator->txt(IConfig::CNF_CUSTOM_FROM_EMAIL))
            ->withValue($this->config->getNotificationSenderAddress() ?? '')
            ->withAdditionalTransformation(
                $this->getEmailValidationConstraint()
            )
        ;

        $inputs[IConfig::CNF_TOOL_IS_ENABLED] = $this->fields->optionalGroup([

            IConfig::CNF_TOOL_SHOW_ROUTINES => $this->fields
                ->checkbox($this->translator->txt(IConfig::CNF_TOOL_SHOW_ROUTINES))
                ->withValue($this->config->shouldToolShowRoutines())
            ,

            IConfig::CNF_TOOL_SHOW_CONTROLS => $this->fields
                ->checkbox($this->translator->txt(IConfig::CNF_TOOL_SHOW_CONTROLS))
                ->withValue($this->config->shouldToolShowControls())
            ,

        ], $this->translator->txt(IConfig::CNF_TOOL_IS_ENABLED));

        if (!$this->config->isToolEnabled()) {
            $inputs[IConfig::CNF_TOOL_IS_ENABLED] = $inputs[IConfig::CNF_TOOL_IS_ENABLED]->withValue(null);
        }

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }
}