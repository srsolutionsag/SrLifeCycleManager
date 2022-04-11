<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Whitelist\WhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This GUI class is responsible for all actions regarding whitelist entries.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistGUI extends ilSrAbstractGUI
{
    // ilSrWhitelistGUI command/method names:
    public const CMD_ROUTINE_OPT_OUT = 'optOut';
    public const CMD_ROUTINE_EXTEND  = 'extend';

    // ilSrWhitelistGUI language variables:
    protected const MSG_ROUTINE_EXTENDED = 'msg_routine_extended';
    protected const MSG_ROUTINE_ALREADY_EXTENDED = 'msg_routine_already_extended';
    protected const MSG_ROUTINE_CANT_EXTEND = 'msg_routine_cant_extend';
    protected const MSG_ROUTINE_CANT_OPT_OUT = 'msg_routine_cant_opt_out';
    protected const MSG_ROUTINE_OPTED_OUT = 'msg_routine_opted_out';
    protected const MSG_ROUTINE_ALREADY_OPTED_OUT = 'msg_routine_already_opted_out';
    protected const PAGE_TITLE = 'page_title_whitelist';

    /**
     * Panics if the request is missing an existing routine.
     *
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->panicOnMissingRoutine();
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
        ;
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        // index method is accessible for routine managers to see
        // which objects are currently whitelisted.
        if (self::CMD_INDEX === $command) {
            return $access_handler->canManageRoutines();
        }

        // all other actions (opt-out, postpone) are only accessible
        // for object-administrators.
        if (null !== $this->object_ref_id) {
            return $access_handler->isAdministratorOf($this->object_ref_id);
        }

        return false;
    }

    /**
     * Displays a table with all whitelist entries for the currently
     * requested routine.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $table = new ilSrWhitelistTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->whitelist()->getByRoutine($this->routine, true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->render($table->getTable());
    }

    /**
     * Extends the requested object for the possible amount of days
     * from the current routine.
     *
     * If the request object or routine wasn't provided the page will
     * display an error message.
     *
     * Otherwise, the user will be redirected back to the requested
     * object with an according info-message.
     */
    protected function extend() : void
    {
        // abort if the requested routine has not been stored yet or
        // no target object was provided.
        if (null === $this->object_ref_id ||
            null === $this->routine->getRoutineId()
        ) {
            $this->sendErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            $this->cancel();
        }

        // abort if the requested routine does not support elongations.
        if (1 > $this->routine->getElongation()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_CANT_EXTEND);
            $this->cancel();
        }

        $whitelist_entry = $this->repository->whitelist()->get($this->routine, $this->object_ref_id);
        if (null !== $whitelist_entry) {
            $this->sendInfoMessage(self::MSG_ROUTINE_ALREADY_EXTENDED);
            $this->cancel();
        }

        $notifications = $this->repository->notification()->getSentNotifications(
            $this->routine,
            $this->object_ref_id
        );

        // if a notification has already been sent, the remaining amount
        // of elongation must be calculated.
        if (!empty($notifications)) {
            $last_notification = $notifications[count($notifications) - 1];
            $elongation = $this->getRemainingElongation($last_notification, $this->routine);
            if (0 >= $elongation) {
                $this->sendErrorMessage(self::MSG_ROUTINE_CANT_EXTEND);
                $this->cancel();
            }
        } else {
            $elongation = $this->routine->getElongation();
        }

        $this->repository->whitelist()->store(
            new WhitelistEntry(
                $this->routine->getRoutineId(),
                $this->object_ref_id,
                $this->user->getId(),
                false,
                new DateTimeImmutable(),
                $elongation
            )
        );

        $message = str_replace(
            '[ELONGATION]',
            (string) $elongation,
            $this->translator->txt(self::MSG_ROUTINE_EXTENDED)
        );

        // redirect back to the target object with according message.
        ilUtil::sendSuccess($message, true);
        $this->cancel();
    }

    /**
     * Opts-out the requested object from the current routine.
     *
     * If the request object or routine wasn't provided the page will
     * display an error message.
     *
     * Otherwise, the user will be redirected back to the requested
     * object with an according info-message.
     */
    protected function optOut() : void
    {
        // abort if the requested routine has not been stored yet or
        // no target object was provided.
        if (null === $this->object_ref_id ||
            null === $this->routine->getRoutineId()
        ) {
            $this->sendErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            $this->cancel();
        }

        // abort if the requested routine does not support elongations.
        if (!$this->routine->hasOptOut()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_CANT_OPT_OUT);
            $this->cancel();
        }

        $whitelist_entry = $this->repository->whitelist()->get($this->routine, $this->object_ref_id);
        if (null !== $whitelist_entry && $whitelist_entry->isOptOut()) {
            $this->sendInfoMessage(self::MSG_ROUTINE_ALREADY_OPTED_OUT);
            $this->cancel();
        }

        $this->repository->whitelist()->store(
            new WhitelistEntry(
                $this->routine->getRoutineId(),
                $this->object_ref_id,
                $this->user->getId(),
                true,
                new DateTimeImmutable()
            )
        );

        // redirect back to the target object with according message.
        $this->sendSuccessMessage(self::MSG_ROUTINE_OPTED_OUT);
        $this->cancel();
    }

    /**
     * Calculates the remaining elongation that is possible for the given
     * notification.
     *
     * This needs to be done, so already sent notifications that contain
     * extension links can't postpone one day before deletion and still
     * be able to extend the full amount.
     *
     * @param INotification $notification
     * @param IRoutine      $routine
     * @return int
     */
    protected function getRemainingElongation(INotification $notification, IRoutine $routine) : int
    {
        $elongation_from = $notification->getNotifiedDate()->add(
            new DateInterval("P{$notification->getDaysBeforeSubmission()}D")
        );

        $elongation_until = $elongation_from->add(
            new DateInterval("P{$routine->getElongation()}D")
        );

        // return the gap to $elongation_until and allow negative values
        // with '%r', so that past until-dates can be detected.
        return (int) (new DateTime())
            ->diff($elongation_until)
            ->format("%r%a")
        ;
    }

    /**
     * Override cancel method to redirect to the requested object instead
     * of the index command, if one is provided.
     *
     * @inheritDoc
     */
    protected function cancel() : void
    {
        if (null !== $this->object_ref_id) {
            $this->ctrl->redirectToURL(ilLink::_getLink($this->object_ref_id));
        }

        parent::cancel();
    }
}