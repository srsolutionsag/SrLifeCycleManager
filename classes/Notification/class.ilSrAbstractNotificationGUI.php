<?php declare(strict_types=1);

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractNotificationGUI extends ilSrAbstractGUI
{
    // ilSrNotificationGUI GET-parameter names:
    public const PARAM_NOTIFICATION_ID = 'notification_id';

    // ilSrNotificationGUI command/method names:
    public const CMD_NOTIFICATION_EDIT   = 'edit';
    public const CMD_NOTIFICATION_SAVE   = 'save';
    public const CMD_NOTIFICATION_DELETE = 'delete';

    // ilSrNotificationGUI language variables:
    protected const MSG_NOTIFICATION_SUCCESS = 'msg_notification_success';
    protected const MSG_NOTIFICATION_ERROR = 'msg_notification_error';
    protected const PAGE_TITLE = 'page_title_notifications';

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
            ->addPreviewTab()
            ->deactivateTabs()
            ->addBackToIndex(static::class)
        ;
    }

    /**
     * Displays the notification form on the current page.
     *
     * If a notification is requested, the form-director already was
     * initialized with the according data, therefore this method can
     * be used for create AND update commands.
     */
    abstract protected function edit() : void;

    /**
     * Processes the submitted notification-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrAbstractNotificationGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    abstract protected function save() : void;

    /**
     * Deletes the requested routine and redirects the user back to
     * @see ilSrAbstractNotificationGUI::index().
     */
    abstract protected function delete() : void;
}
