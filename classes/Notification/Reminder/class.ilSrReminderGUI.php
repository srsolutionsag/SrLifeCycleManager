<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Form\Notification\ReminderFormProcessor;
use srag\Plugins\SrLifeCycleManager\Form\Notification\ReminderFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;

/**
 * This GUI class is responsible for all actions regarding notifications.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrReminderGUI extends ilSrAbstractNotificationGUI
{
    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * @var IReminder
     */
    protected $notification;

    /**
     * Initializes the notification form-builder and fetches the requested
     * notification from the current request.
     */
    public function __construct()
    {
        parent::__construct();

        $this->panicOnMissingRoutine();

        $this->notification =
            $this->getRequestedNotification() ??
            $this->repository->reminder()->empty($this->routine)
        ;

        $this->form_builder = new ReminderFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->repository->reminder(),
            $this->notification,
            $this->getFormAction(
                self::CMD_NOTIFICATION_SAVE,
                self::PARAM_NOTIFICATION_ID
            )
        );
    }

    /**
     * Fetches the requested notification from the database if an id was provided.
     *
     * @return IReminder|null
     */
    protected function getRequestedNotification(): ?IReminder
    {
        $notification_id = $this->getRequestParameter(self::PARAM_NOTIFICATION_ID);
        if (null !== $notification_id) {
            return $this->repository->reminder()->get((int) $notification_id);
        }

        return null;
    }

    /**
     * Displays all existing reminders that are related to the requested routine.
     *
     * @inheritDoc
     */
    protected function index(): void
    {
        $table = new ilSrReminderTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->repository->general(),
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->reminder()->getByRoutine($this->routine, true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->toolbar_manager->addReminderToolbar();
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
        $processor = new ReminderFormProcessor(
            $this->repository->reminder(),
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
            $this->repository->reminder()->delete($this->notification);
        } else {
            $this->sendErrorMessage(self::MSG_NOTIFICATION_ERROR);
        }

        $this->cancel();
    }
}
