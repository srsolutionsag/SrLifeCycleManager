<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineNotification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationGUI extends ilSrAbstractGUI
{
    public const QUERY_PARAM_NOTIFICATION_ID = 'notification_id';

    protected const PAGE_TITLE               = 'page_title_notifications';
    protected const MSG_ROUTINE_NOT_FOUND    = 'msg_routine_not_found';
    protected const MSG_NOTIFICATION_SUCCESS = 'msg_notification_success';
    protected const MSG_NOTIFICATION_ERROR   = 'msg_notification_error';

    public const CMD_NOTIFICATION_ADD    = 'add';
    public const CMD_NOTIFICATION_SAVE   = 'save';
    public const CMD_NOTIFICATION_EDIT   = 'edit';
    public const CMD_NOTIFICATION_DELETE = 'delete';

    /**
     * @var IRoutine|null
     */
    protected $routine;

    /**
     * @var INotification|null
     */
    protected $notification;

    /**
     * @var IRoutineNotification|null
     */
    protected $routine_notification_relation = null;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->routine = $this->getRoutineFromRequest(true);
        $this->notification = $this->getNotificationFromRequest();

        if (null !== $this->notification) {
            $this->routine_notification_relation = $this->repository
                ->routine()
                ->getNotificationRelation(
                    $this->routine,
                    $this->notification
                )
            ;
        }
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
            self::CMD_NOTIFICATION_ADD,
            self::CMD_NOTIFICATION_SAVE,
            self::CMD_NOTIFICATION_EDIT,
            self::CMD_NOTIFICATION_DELETE,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecuteCommand(int $user_id, string $command) : bool
    {
        // all actions implemented by this GUI require the
        // user to be assigned to at least one configured
        // global role, or the administrator role.
        return ilSrAccess::isUserAssignedToConfiguredRole($user_id) || ilSrAccess::isUserAdministrator($user_id);
    }

    /**
     * @inheritDoc
     */
    protected function beforeCommand(string $command) : void
    {
        // abort if no routine was provided, as all actions
        // of this GUI depend on it.
        if (null === $this->routine) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }

        // add the configuration tabs to the current page
        // and deactivate all tabs by passing an invalid
        // character as active tab-id.
        $this->addConfigurationTabs('§');
    }

    /**
     * Displays a notification table on the current page.
     *
     * The table lists all existing notifications related to the
     * routine-id provided as a GET parameter.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        // override the back-to tab with one that redirects back
        // to the routine GUI.
        $this->overrideBack2Target(
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineGUI::class,
                self::CMD_INDEX
            )
        );

        $this->ui->mainTemplate()->setContent(
            $this->getTable()->getHTML()
        );
    }

    /**
     * Returns a notification fetched from the database for an id provided
     * as a GET parameter.
     *
     * This method is implemented here, as it's used in several derived
     * classes and is somewhat core to the plugin.
     *
     * @param bool $keep_alive
     * @return INotification|null
     */
    protected function getNotificationFromRequest(bool $keep_alive = false) : ?INotification
    {
        $notification_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_NOTIFICATION_ID, $keep_alive);
        if (null !== $notification_id) {
            return $this->repository->notification()->get((int) $notification_id);
        }

        return null;
    }

    /**
     * Gathers all existing notifications related to the current routine.
     *
     * @return array
     */
    protected function getTableData() : array
    {
        return $this->repository->routine()->getNotificationTableData($this->routine);
    }

    /**
     * Returns the notification form action.
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        // pass along the routine id so the relationship can be built.
        $this->ctrl->setParameterByClass(
            self::class,
            self::QUERY_PARAM_ROUTINE_ID,
            $this->routine->getId()
        );

        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_NOTIFICATION_SAVE
        );
    }

    /**
     * Helper function that initializes and returns the
     * notifications form.
     *
     * @return ilSrNotificationForm
     */
    protected function getForm() : ilSrNotificationForm
    {
        return new ilSrNotificationForm(
            $this->repository,
            $this->ui->mainTemplate(),
            $this->ui->renderer(),
            $this->form_builders
                ->notification()
                ->withNotification($this->notification)
                ->withRoutineRelation($this->routine_notification_relation)
                ->getForm($this->getFormAction()),
            $this->routine,
            $this->notification
        );
    }

    /**
     * Helper function that initializes and returns the
     * notifications table.
     *
     * @return ilSrNotificationTable
     */
    protected function getTable() : ilSrNotificationTable
    {
        return new ilSrNotificationTable(
            $this->ui,
            $this->plugin,
            $this,
            self::CMD_INDEX,
            'tpl.notification_row.html',
            $this->getTableData()
        );
    }
}