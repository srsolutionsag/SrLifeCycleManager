<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Config\ConfigFormProcessor;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Filesystem\Stream\Streams;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This GUI is responsible for all actions in regard to plugin configuration.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
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
    protected ConfigFormBuilder $form_builder;

    /**
     * @var GlobalHttpState
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
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab(true)
            ->addRoutineTab()
            ->addPreviewTab();
        // if the current user is not within the administration context we
        // need to add the back-to target manually.
        if (null === $this->object_ref_id) {
            return;
        }
        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION === $this->origin) {
            return;
        }
        $tabs->addBackToObject($this->object_ref_id);
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        // the configurations are only accessible for administrators.
        return $access_handler->isAdministrator();
    }

    /**
     * Displays the plugin configuration form.
     *
     * @inheritDoc
     */
    protected function index(): void
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
    protected function save(): void
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
    protected function findUsers(): void
    {
        $body = $this->request->getQueryParams();
        $term = $body['term'] ?? '';

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody(
                    Streams::ofString(
                        json_encode(
                            $this->repository->general()->getUsersByTerm($term)
                        )
                    )
                )
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Returns an ajax autocomplete source that points to @return string
     * @see ilSrConfigGUI::findUsers().
     *
     */
    protected function getAjaxAction(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_SEARCH,
            "",
            true
        );
    }
}
