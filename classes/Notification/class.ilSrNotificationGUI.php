<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Form\Notification\NotificationForm;
use srag\Plugins\SrLifeCycleManager\Form\Notification\NotificationFormBuilder;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationGUI extends ilSrAbstractGUI
{
    public const QUERY_PARAM_NOTIFICATION_ID = 'notification_id';

    public const CMD_NOTIFICATION_ADD    = 'add';
    public const CMD_NOTIFICATION_SAVE   = 'save';
    public const CMD_NOTIFICATION_EDIT   = 'add';
    public const CMD_NOTIFICATION_DELETE = 'delete';

    public const ACTION_NOTIFICATION_ADD = 'action_notification_add';
    public const ACTION_NOTIFICATION_EDIT = 'action_notification_edit';
    public const ACTION_NOTIFICATION_DELETE = 'action_notification_delete';

    protected const PAGE_TITLE               = 'page_title_notifications';
    protected const MSG_ROUTINE_NOT_FOUND    = 'msg_routine_not_found';
    protected const MSG_NOTIFICATION_SUCCESS = 'msg_notification_success';
    protected const MSG_NOTIFICATION_ERROR   = 'msg_notification_error';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var IRoutineAwareNotification|null
     */
    protected $notification;

    /**
     * @var int|null
     */
    protected $scope;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->routine = $this->getRoutineFromRequest(true);
        $this->notification = $this->getNotificationFromRequest();
        $this->scope = $this->getScopeFromRequest();
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
        // administrators should be able to execute all commands.
        if (ilSrAccess::isUserAdministrator($user_id)) {
            return true;
        }

        // if the current user is the owner of the related routine,
        // he should be able to execute all commands too.
        if (null !== $this->routine && ilSrAccess::isUserAssignedToConfiguredRole($user_id)) {
            return ($user_id === $this->routine->getOwnerId());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function beforeCommand(string $command) : void
    {
        // add the configuration tabs to the current page
        // and deactivate all tabs by passing an invalid
        // character as active tab-id.
        $this->addConfigurationTabs('§');

        // abort if no routine was provided, as all actions
        // of this GUI depend on it.
        if (null === $this->routine) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }
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

        $this->addNotificationToolbar();
        $this->ui->mainTemplate()->setContent(
            $this->getTable()->getHTML()
        );
    }

    /**
     * Displays a notification form on the current page.
     *
     * This method does however not process it, form submissions are
     * sent to @see ilSrNotificationGUI::save().
     */
    protected function add() : void
    {
        $this->ui->mainTemplate()->setContent(
            $this->getForm()->render()
        );
    }

    /**
     * Processes the submitted form-data and creates a new notification
     * that is related to the current routine.
     *
     * If the creation fails or any inputs are invalid, the form will
     * be displayed again with an according error message.
     */
    protected function save() : void
    {
        $form = $this->getForm();
        if ($form->handleRequest($this->http->request())) {
            $this->displaySuccessMessage(self::MSG_NOTIFICATION_SUCCESS);
            $this->repeat();
        }

        $this->ui->mainTemplate()->setContent(
            $form->render()
        );
    }

    /**
     * Deletes an existing notification and relation to the current routine.
     */
    protected function delete() : void
    {
        $notification = $this->getNotificationFromRequest();
        if (null !== $this->routine && null !== $notification) {
            $this->repository->notification()->delete($notification);
            $this->sendSuccessMessage(self::MSG_NOTIFICATION_SUCCESS);
        } else {
            $this->sendErrorMessage(self::MSG_NOTIFICATION_ERROR);
        }

        $this->repeat();
    }

    /**
     * Displays a notification action-toolbar on the current page.
     *
     * The toolbar SHOULD contain actions that cannot be implemented
     * or added to a table-row-entry's dropdown actions (like add
     * for example).
     */
    protected function addNotificationToolbar() : void
    {
        // create a button instance to create new routines.
        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption($this->plugin->txt(self::ACTION_NOTIFICATION_ADD), false);
        $button->setUrl($this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_NOTIFICATION_ADD
        ));

        $this->toolbar->addButtonInstance($button);
        $this->ui->mainTemplate()->setContent($this->toolbar->getHTML());
    }

    /**
     * Returns a notification fetched from the database for an id provided
     * as a GET parameter.
     *
     * This method is implemented here, as it's used in several derived
     * classes and is somewhat core to the plugin.
     *
     * @param bool $keep_alive
     * @return IRoutineAwareNotification|null
     */
    protected function getNotificationFromRequest(bool $keep_alive = false) : ?IRoutineAwareNotification
    {
        $notification_id = $this->getQueryParamFromRequest(self::QUERY_PARAM_NOTIFICATION_ID, $keep_alive);
        if (null !== $notification_id) {
            return $this->repository->notification()->get(
                $this->routine->getRoutineId(),
                (int) $notification_id
            );
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
        return $this->repository->notification()->getAll($this->routine->getRoutineId(), true);
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
            $this->routine->getRoutineId()
        );

        // pass along the notification id in case one was being edited.
        if (null !== $this->notification) {
            $this->ctrl->setParameterByClass(
                self::class,
                self::QUERY_PARAM_NOTIFICATION_ID,
                $this->notification->getNotificationId()
            );
        }

        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_NOTIFICATION_SAVE
        );
    }

    /**
     * Helper function that initializes and returns the
     * notifications form.
     *
     * @return NotificationForm
     */
    protected function getForm() : NotificationForm
    {
        $builder = new NotificationFormBuilder(
            $this->ui->factory()->input()->container()->form(),
            $this->ui->factory()->input()->field(),
            $this->refinery,
            $this->plugin,
            $this->getFormAction(),
            $this->notification ?? $this->repository->notification()->getEmpty(
                $this->routine->getRoutineId()
            )
        );

        return new NotificationForm(
            $this->repository,
            $this->ui->renderer(),
            $builder
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
            'tpl.notification_table_row.html',
            $this->getTableData()
        );
    }
}