<?php

declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use srag\Plugins\SrLifeCycleManager\Form\Notification\ConfirmationFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Notification\ConfirmationFormProcessor;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineEvent;

/**
 * This GUI class is responsible for all actions regarding confirmations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrConfirmationGUI extends ilSrAbstractNotificationGUI
{
    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * @var IConfirmation
     */
    protected $notification;

    /**
     * Initializes the confirmation form-builder and fetches the requested
     * confirmation from the current request.
     *
     * @throws LogicException if no routine (id) has been requested.
     */
    public function __construct()
    {
        parent::__construct();

        $this->panicOnMissingRoutine();

        $this->notification =
            $this->getRequestedNotification() ??
            $this->repository->confirmation()->empty($this->routine);

        $this->form_builder = new ConfirmationFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->repository->confirmation(),
            $this->notification,
            $this->getConfirmationEventOptions(),
            $this->getFormAction(
                self::CMD_NOTIFICATION_SAVE,
                self::PARAM_NOTIFICATION_ID
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        // only routine-managers can execute commands in this gui.
        return $this->access_handler->canManageRoutines();
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab()
            ->addRoutineTab()
            ->addPreviewTab()
            ->deactivateTabs()
            ->addBackToIndex(static::class);
    }

    /**
     * Fetches the requested confirmation from the database if an id was provided.
     *
     * @return IConfirmation|null
     */
    protected function getRequestedNotification(): ?IConfirmation
    {
        $notification_id = $this->getRequestParameter(self::PARAM_NOTIFICATION_ID);
        if (null !== $notification_id) {
            return $this->repository->confirmation()->get((int) $notification_id);
        }

        return null;
    }

    /**
     * Displays all existing confirmations that are related to the requested routine.
     *
     * @inheritDoc
     */
    protected function index(): void
    {
        $table = new ilSrConfirmationTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->confirmation()->getByRoutine($this->routine, true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->toolbar_manager->addConfirmationToolbar();
        $this->render($table->getTable());
    }

    /**
     * @inheritDoc
     */
    protected function edit(): void
    {
        $this->render($this->form_builder->getForm());
    }

    /**
     * @inheritDoc
     */
    protected function save(): void
    {
        $processor = new ConfirmationFormProcessor(
            $this->repository->confirmation(),
            $this->request,
            $this->form_builder->getForm(),
            $this->notification
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_NOTIFICATION_SUCCESS);
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * @inheritDoc
     */
    protected function delete(): void
    {
        if (null !== $this->notification) {
            $this->sendSuccessMessage(self::MSG_NOTIFICATION_SUCCESS);
            $this->repository->confirmation()->delete($this->notification);
        } else {
            $this->sendErrorMessage(self::MSG_NOTIFICATION_ERROR);
        }

        $this->cancel();
    }

    /**
     * Returns routine events which can trigger confirmations.
     *
     * @return string[]
     */
    protected function getConfirmationEventOptions(): array
    {
        return [
            IRoutineEvent::EVENT_POSTPONE,
            IRoutineEvent::EVENT_OPT_OUT,
            IRoutineEvent::EVENT_DELETE,
        ];
    }
}
