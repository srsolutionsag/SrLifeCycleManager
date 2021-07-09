<?php

/**
 * Class ilSrConfigForm is responsible for the configuration form.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilSrConfigForm extends ilSrAbstractMainForm
{
    /**
     * @var ilSrConfig[]
     */
    private $config;

    /**
     * @var ilSetting
     */
    private $settings;

    /**
     * ilSrConfigForm constructor.
     */
    public function __construct()
    {
        global $DIC;

        // dependencies must be declared before the parent constructor
        // is called, as they're already used by it.
        $this->config   = ilSrConfig::get();
        $this->settings = $DIC->settings();

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            ilSrConfigGUI::class,
            ilSrConfigGUI::CMD_CONFIG_SAVE
        );
    }

    /**
     * @inheritDoc
     */
    protected function getFormInputs() : array
    {
        $inputs = [];

        $inputs[ilSrConfig::CNF_GLOBAL_ROLES] = $this->inputs->multiSelect(
            $this->plugin->txt(ilSrConfig::CNF_GLOBAL_ROLES),
            $this->repository->getGlobalRoleOptions()
        )
        ->withValue((isset($this->config[ilSrConfig::CNF_GLOBAL_ROLES])) ?
            $this->config[ilSrConfig::CNF_GLOBAL_ROLES]->getValue() : []
        );

        // add move-to-bin input only if ILIAS trash is enabled.
        if ($this->settings->get('enable_trash')) {
            $inputs[ilSrConfig::CNF_MOVE_TO_BIN] = $this->inputs->checkbox(
                $this->plugin->txt(ilSrConfig::CNF_MOVE_TO_BIN)
            )
            ->withValue(
                isset($this->config[ilSrConfig::CNF_MOVE_TO_BIN]) &&
                $this->config[ilSrConfig::CNF_MOVE_TO_BIN]->getValue()
            );
        }

        $inputs[ilSrConfig::CNF_SHOW_ROUTINES] = $this->inputs->checkbox(
            $this->plugin->txt(ilSrConfig::CNF_SHOW_ROUTINES)
        )
        ->withValue(
            isset($this->config[ilSrConfig::CNF_SHOW_ROUTINES]) &&
            $this->config[ilSrConfig::CNF_SHOW_ROUTINES]->getValue()
        );

        $inputs[ilSrConfig::CNF_CREATE_ROUTINES] = $this->inputs->checkbox(
            $this->plugin->txt(ilSrConfig::CNF_CREATE_ROUTINES)
        )
        ->withValue(
            isset($this->config[ilSrConfig::CNF_CREATE_ROUTINES]) &&
            $this->config[ilSrConfig::CNF_CREATE_ROUTINES]->getValue()
        );

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // the submitted form_data is always valid, as it's
        // possible all inputs were unchecked or removed.
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        foreach ($form_data as $identifier => $value) {
            // try to find an existing database entry for current
            // $identifier or create a new instance.
            $config = ilSrConfig::find($identifier) ?? new ilSrConfig();
            $config
                // this may be redundant, but more performant than if-else
                ->setIdentifier($identifier)
                ->setValue($value)
                ->store()
            ;
        }
    }
}