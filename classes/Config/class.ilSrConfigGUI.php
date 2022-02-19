<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigForm;
use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigFormBuilder;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * Class ilSrConfigGUI is responsible for the general plugin configuration.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This class is called whenever the plugin-configuration within the
 * plugin administration is requested.
 *
 * It's responsible for general configuration and displays a form on
 * the first request. Further, commands implemented in this class are
 * the change and store these configurations.
 */
class ilSrConfigGUI extends ilSrAbstractGUI
{
    /**
     * ilSrConfigGUI command names (methods)
     */
    public const CMD_CONFIG_SAVE  = 'save';

    /**
     * ilSrConfigGUI lang vars
     */
    protected const MSG_CONFIGURATION_SUCCESS = 'msg_configuration_success';
    protected const MSG_CONFIGURATION_ERROR   = 'msg_configuration_error';
    protected const PAGE_TITLE                = 'page_title_config';

    /**
     * @var ConfigFormBuilder
     */
    protected $form_builder;

    /**
     * ilSrConfigGUI constructor
     */
    public function __construct()
    {
        parent::__construct();

        global $DIC;

        /** @var $config IConfig[] */
        $config = ilSrConfig::get();

        $this->form_builder = new ConfigFormBuilder(
            $this->ui->factory()->input()->container()->form(),
            $this->ui->factory()->input()->field(),
            $this->refinery,
            $this->plugin,
            $this->getFormAction(),
            $config,
            $this->repository->getGlobalRoleOptions(),
            (bool) $DIC->settings()->get('enable_trash')
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template) : void
    {
        $template->setTitle($this->plugin->txt(self::PAGE_TITLE));
    }

    /**
     * @inheritDoc
     */
    protected function getCommandList() : array
    {
        return [
            self::CMD_INDEX,
            self::CMD_CONFIG_SAVE,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecuteCommand(int $user_id, string $command) : bool
    {
        // all actions implemented by this GUI require at least
        // administrator privileges, hence $command is ignored.
        return ilSrAccess::isUserAdministrator($user_id);
    }

    /**
     * @inheritDoc
     */
    protected function beforeCommand(string $command) : void
    {
        // adds the configuration tabs to the current page
        // before each command is executed.
        $this->addConfigurationTabs(self::TAB_CONFIG_INDEX);
    }

    /**
     * Displays the general configuration form on the current page.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
    }

    /**
     * Stores the changed or added configuration to the database
     * and redirects to the index.
     */
    protected function save() : void
    {
        $form = $this->getForm();
        if ($form->handleRequest($this->http->request())) {
            $this->sendSuccessMessage(self::MSG_CONFIGURATION_SUCCESS);
            $this->repeat();
        }

        $this->sendErrorMessage(self::MSG_CONFIGURATION_ERROR);
        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
    }

    /**
     * Returns the configuration form action.
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_CONFIG_SAVE
        );
    }

    /**
     * Helper function that initialises the configuration form and
     * returns it.
     *
     * @return ConfigForm
     */
    protected function getForm() : ConfigForm
    {
        return new ConfigForm(
            $this->repository,
            $this->ui->renderer(),
            $this->form_builder
        );
    }
}