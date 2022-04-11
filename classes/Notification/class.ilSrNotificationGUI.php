<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Form\Notification\NotificationFormProcessor;
use srag\Plugins\SrLifeCycleManager\Form\Notification\NotificationFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;

/**
 * This GUI class is responsible for all actions regarding notifications.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrNotificationGUI extends ilSrAbstractGUI
{
    // ilSrNotificationGUI GET-parameter names:
    public const PARAM_NOTIFICATION_ID = 'notification_id';

    // ilSrNotificationGUI command/method names:
    public const CMD_NOTIFICATION_EDIT   = 'edit';
    public const CMD_NOTIFICATION_SAVE   = 'save';
    public const CMD_NOTIFICATION_DELETE = 'delete';

    // ilSrNotificationGUI language variables:
    protected const MSG_ROUTINE_NOT_FOUND = 'msg_routine_not_found';
    protected const MSG_NOTIFICATION_SUCCESS = 'msg_notification_success';
    protected const MSG_NOTIFICATION_ERROR = 'msg_notification_error';
    protected const PAGE_TITLE = 'page_title_notifications';

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * @var INotification
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
            $this->repository->notification()->empty($this->routine)
        ;

        $this->form_builder = new NotificationFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->repository->notification(),
            $this->notification,
            $this->getFormAction()
        );
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        // only routine-managers can execute commands in this gui.
        return $this->access_handler->canManageRoutines();
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs) : void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab()
            ->addRoutineTab()
            ->deactivateTabs()
            ->setBackToTarget(
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    self::CMD_INDEX
                )
            )
        ;
    }

    /**
     * Fetches the requested notification from the database if an id was provided.
     *
     * @return INotification|null
     */
    protected function getRequestedNotification() : ?INotification
    {
        $notification_id = $this->getRequestParameter(self::PARAM_NOTIFICATION_ID);
        if (null !== $notification_id) {
            return $this->repository->notification()->get((int) $notification_id);
        }

        return null;
    }

    /**
      * Displays all existing notifications that are related to the requested routine.
     */
    protected function index() : void
    {
        $table = new ilSrNotificationTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->notification()->getByRoutine($this->routine, true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->toolbar_manager->addNotificationToolbar();
        $this->render($table->getTable());
    }

    /**
     * Displays the notification form on the current page.
     *
     * If a notification is requested, the form-director already was
     * initialized with the according data, therefore this method can
     * be used for create AND update commands.
     */
    protected function edit() : void
    {
        $this->render($this->form_builder->getForm());
    }

    /**
     * Processes the submitted notification-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrNotificationGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    protected function save() : void
    {
        $processor = new NotificationFormProcessor(
            $this->repository->notification(),
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
     * Deletes the requested routine and redirects the user back to
     * @see ilSrNotificationGUI::index().
     */
    protected function delete() : void
    {
        if (null !== $this->notification) {
            $this->sendSuccessMessage(self::MSG_NOTIFICATION_SUCCESS);
            $this->repository->notification()->delete($this->notification);
        } else {
            $this->sendErrorMessage(self::MSG_NOTIFICATION_ERROR);
        }

        $this->cancel();
    }

    /**
     * Returns the notification form-action pointing to @see ilSrNotificationGUI::save().
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_NOTIFICATION_SAVE
        );
    }
}