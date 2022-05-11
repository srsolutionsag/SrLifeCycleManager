<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigFormProcessor;
use ILIAS\DI\HTTPServices;
use ILIAS\Filesystem\Stream\Streams;

/**
 * This GUI is responsible for all actions in regard to plugin configuration.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrConfigGUI extends ilSrAbstractGUI
{
    // ilSrConfigGUI command/method names:
    public const CMD_CONFIG_SAVE = 'save';
    public const CMD_SEARCH = 'findUsers';

    // ilSrConfigGUI language variables:
    protected const MSG_CONFIGURATION_SUCCESS = 'msg_configuration_success';
    protected const MSG_CONFIGURATION_ERROR = 'msg_configuration_error';
    protected const PAGE_TITLE = 'page_title_config';

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * @var HTTPServices
     */
    protected $http;

    /**
     * Initializes the configuration form-builder.
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->http = $DIC->http();
        $this->form_builder = new ConfigFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->repository->config()->get(),
            $this->repository->general()->getAvailableGlobalRoles(),
            $this->getFormAction(self::CMD_CONFIG_SAVE),
            $this->getAjaxAction()
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs) : void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab(true)
            ->addRoutineTab()
            ->addPreviewTab()
        ;
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        // the configurations are only accessible for administrators.
        return $access_handler->isAdministrator();
    }

    /**
     * Displays the plugin configuration form.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->render($this->form_builder->getForm());
    }

    /**
     * Processes the configuration form with the current request.
     *
     * If the submitted data is valid, the configuration is updated
     * and the user is redirected to @see ilSrConfigGUI::index().
     *
     * If the submitted data is invalid, the user will be shown the
     * processed form including the error-messages.
     */
    protected function save() : void
    {
        $processor = new ConfigFormProcessor(
            $this->repository->config(),
            $this->request,
            $this->form_builder->getForm()
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_CONFIGURATION_SUCCESS);
            $this->cancel();
        }

        $this->sendErrorMessage(self::MSG_CONFIGURATION_ERROR);
        $this->render($processor->getProcessedForm());
    }

    /**
     * This method searches objects by the requested term and returns
     * them asynchronously (as a json-response).
     *
     * @see AbstractFormBuilder::getTagInputAutoCompleteBinder()
     */
    protected function findUsers() : void
    {
        $body = $this->request->getQueryParams();
        $term = $body['term'] ?? '';

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody(Streams::ofString(json_encode(
                    $this->repository->general()->getUsersByTerm($term)
                )))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Returns an ajax autocomplete source that points to @see ilSrConfigGUI::findUsers().
     *
     * @return string
     */
    protected function getAjaxAction() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_SEARCH,
            "",
            true
        );
    }
}
