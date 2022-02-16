<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form;

use srag\Plugins\SrLifeCycleManager\Builder\Form\Notification\NotificationFormBuilder;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Rule\RuleFormBuilder;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Config\ConfigFormBuilder;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;
use ilPlugin;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FormBuilderFactory
{
    /**
     * @var NotificationFormBuilder
     */
    protected $notification_form_builder;

    /**
     * @var RoutineFormBuilder
     */
    protected $routine_form_builder;

    /**
     * @var RuleFormBuilder
     */
    protected $rule_form_builder;

    /**
     * @var ConfigFormBuilder
     */
    protected $config_form_builder;

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
        $this->notification_form_builder = new NotificationFormBuilder($input_factory, $form_factory, $refinery, $plugin);
        $this->routine_form_builder = new RoutineFormBuilder($input_factory, $form_factory, $refinery, $plugin);
        $this->rule_form_builder = new RuleFormBuilder($input_factory, $form_factory, $refinery, $plugin);
        $this->config_form_builder = new ConfigFormBuilder($input_factory, $form_factory, $refinery, $plugin);
    }

    /**
     * @return NotificationFormBuilder
     */
    public function notification() : NotificationFormBuilder
    {
        return $this->notification_form_builder;
    }

    /**
     * @return RoutineFormBuilder
     */
    public function routine() : RoutineFormBuilder
    {
        return $this->routine_form_builder;
    }

    /**
     * @return RuleFormBuilder
     */
    public function rule() : RuleFormBuilder
    {
        return $this->rule_form_builder;
    }

    /**
     * @return ConfigFormBuilder
     */
    public function config() : ConfigFormBuilder
    {
        return $this->config_form_builder;
    }
}