<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Event\Observer;
use srag\Plugins\SrLifeCycleManager\Token\IToken;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistGUI extends ilSrAbstractGUI
{
    use DateTimeHelper;

    // ilSrWhitelistGUI query parameters:
    public const PARAM_WHITELIST_TOKEN = 'whitelist_token';

    // ilSrWhitelistGUI commands:
    public const CMD_WHITELIST_POSTPONE = 'postpone';
    public const CMD_WHITELIST_OPT_OUT = 'optOut';
    public const CMD_WHITELIST_OPT_OUT_UNDO = 'undoOptOut';

    // ilSrWhitelistGUI language variables:
    protected const MSG_ROUTINE_EXTENDED = 'msg_routine_extended';
    protected const MSG_ROUTINE_CANT_EXTEND = 'msg_routine_cant_extend';
    protected const MSG_ROUTINE_CANT_OPT_OUT = 'msg_routine_cant_opt_out';
    protected const MSG_ROUTINE_OPTED_OUT = 'msg_routine_opted_out';
    protected const MSG_ROUTINE_ALREADY_OPTED_OUT = 'msg_routine_already_opted_out';
    protected const MSG_WHITELIST_INVALID_TOKEN = 'msg_whitelist_invalid_token';
    protected const MSG_WHITELIST_UNCOOL = 'msg_whitelist_not_cool';
    protected const MSG_ROUTINE_CANT_UNDO_OPT_OUT = 'msg_routine_cant_undo_opt_out';
    protected const MSG_ROUTINE_UNDID_OPT_OUT = 'msg_routine_undid_opt_out';
    protected const PAGE_TITLE = 'page_title_whitelist';

    /**
     * @var Observer
     */
    protected $event_observer;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->event_observer = Observer::getInstance();
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
            ->deactivateTabs();
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        if (self::CMD_INDEX === $command || self::CMD_WHITELIST_OPT_OUT_UNDO === $command) {
            return $access_handler->canManageRoutines();
        }

        // since v1.7.0 the access to the opt-out and postpone command
        // can also be granted via whitelist tokens.
        return true;
    }

    /**
     * Displays a table with all whitelist entries for the currently
     * requested routine.
     *
     * @inheritDoc
     */
    protected function index(): void
    {
        if (null === $this->routine->getRoutineId()) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }

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
     * Postpones a deletion of the requested object for the given routine
     * by creating a whitelist entry.
     *
     * The object and routine is either provided by:
     *
     *      (a) query parameters, when called from the repository context
     *          via tool, or
     *      (b) from the database because they were related to the provided
     *          whitelist token.
     *
     * If a whitelist token has been used it MUST be redeemed via repository
     * to prevent repeatable elongations of an object with the same token.
     */
    protected function postpone(): void
    {
        $request_token = $this->getRequestParameter(self::PARAM_WHITELIST_TOKEN);
        $stored_token = (null !== $request_token) ? $this->repository->token()->getByToken($request_token) : null;

        // abort if the provided whitelist token doesn't exist or
        // is not intended for this action.
        if ((null === $stored_token && !empty($request_token)) ||
            (null !== $stored_token && RoutineEvent::EVENT_POSTPONE !== $stored_token->getEvent())
        ) {
            $this->displayErrorMessage(self::MSG_WHITELIST_INVALID_TOKEN);
            return;
        }

        // abort if no whitelist token was provided and the user
        // is not privileged.
        if (null === $stored_token && !$this->access_handler->canManageRoutines()) {
            $this->displayErrorMessage(self::MSG_PERMISSION_DENIED);
            return;
        }

        // abort if the requested object wasn't found.
        if (null === ($object_instance = $this->getObjectByTokenOrRequest($stored_token))) {
            $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            return;
        }

        // abort if the requested routine wasn't found.
        if (null === ($routine = $this->getRoutineByTokenOrRequest($stored_token))) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }

        // abort if the requested routine doesn't support elongations.
        if (1 > $routine->getElongation()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_CANT_EXTEND);
            $this->cancel($object_instance->getRefId());
            return;
        }

        $whitelist_entry = $this->repository->whitelist()->get($routine, $object_instance->getRefId());

        if (null !== $whitelist_entry) {
            // abort if the whitelist entry is an opt-out.
            if ($whitelist_entry->isOptOut()) {
                $this->sendInfoMessage(self::MSG_ROUTINE_ALREADY_OPTED_OUT);
                $this->cancel($object_instance->getRefId());
                return;
            }

            // abort if there is a cooldown which is not yet elapsed.
            if (null !== ($cooldown = $routine->getElongationCooldown()) &&
                null !== ($whitelist_date = $whitelist_entry->getDate()) &&
                $this->getCurrentDate() < $whitelist_date->add(new DateInterval("P{$cooldown}D"))
            ) {
                $this->sendErrorMessage(self::MSG_WHITELIST_UNCOOL);
                $this->cancel($object_instance->getRefId());
                return;
            }
        }

        // the deletion date is either the previous expiry date or the
        // one calculated by the routine repository.
        $deletion_date = $this->repository->routine()->getDeletionDate($routine, $object_instance->getRefId());
        $deletion_date = (null !== $whitelist_entry) ?
            $whitelist_entry->getExpiryDate() ?? $deletion_date :
            $deletion_date;

        // the next expiry date will be the determined deletion date plus
        // the current routine's elongation.
        $expiry_date = $deletion_date->add(new DateInterval("P{$routine->getElongation()}D"));

        $whitelist_entry = $whitelist_entry ??
            $this->repository->whitelist()->empty(
                $routine,
                $object_instance->getRefId(),
                $this->user->getId()
            );

        // store the existing or a new entry with the updated information.
        $this->repository->whitelist()->store(
            $whitelist_entry
                ->setDate($this->getCurrentDate())
                ->setUserId($this->user->getId())
                ->setExpiryDate($expiry_date)
        );

        // if a whitelist token was being used it must be redeemed.
        if (null !== $stored_token) {
            $this->repository->token()->redeem($stored_token);
        }

        $this->event_observer->broadcast(
            new RoutineEvent(
                $routine,
                $object_instance,
                RoutineEvent::EVENT_POSTPONE
            )
        );

        // redirect back to the target object with according message.
        ilUtil::sendSuccess(
            sprintf(
                $this->translator->txt(self::MSG_ROUTINE_EXTENDED),
                $routine->getElongation()
            ),
            true
        );

        $this->redirectToRefId($object_instance->getRefId());
    }

    /**
     * Postpones a deletion of the requested object for the given routine
     * by creating a whitelist entry.
     *
     * The object and routine is either provided by:
     *
     *      (a) query parameters, when called from the repository context
     *          via tool, or
     *      (b) from the database because they were related to the provided
     *          whitelist token.
     *
     * If a whitelist token has been used it MUST be redeemed via repository
     * to prevent repeatable elongations of an object with the same token.
     */
    protected function optOut(): void
    {
        $request_token = $this->getRequestParameter(self::PARAM_WHITELIST_TOKEN);
        $stored_token = (null !== $request_token) ? $this->repository->token()->getByToken($request_token) : null;

        // abort if the provided whitelist token doesn't exist or
        // is not intended for this action.
        if ((null === $stored_token && !empty($request_token)) ||
            (null !== $stored_token && RoutineEvent::EVENT_OPT_OUT !== $stored_token->getEvent())
        ) {
            $this->displayErrorMessage(self::MSG_WHITELIST_INVALID_TOKEN);
            return;
        }

        // abort if no whitelist token was provided and the user
        // is not privileged.
        if (null === $stored_token && !$this->access_handler->canManageRoutines()) {
            $this->displayErrorMessage(self::MSG_PERMISSION_DENIED);
            return;
        }

        // abort if the requested object wasn't found.
        if (null === ($object_instance = $this->getObjectByTokenOrRequest($stored_token))) {
            $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            return;
        }

        // abort if the requested routine wasn't found.
        if (null === ($routine = $this->getRoutineByTokenOrRequest($stored_token))) {
            $this->displayErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            return;
        }

        // abort if the requested routine doesn't support opt-outs
        // and the current user is no administrator.
        if (!$routine->hasOptOut() && !$this->access_handler->isAdministrator()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_CANT_OPT_OUT);
            $this->cancel($object_instance->getRefId());
            return;
        }

        $whitelist_entry = $this->repository->whitelist()->get($routine, $object_instance->getRefId());

        // abort if the whitelist entry is an opt-out.
        if ((null !== $whitelist_entry) && $whitelist_entry->isOptOut()) {
            $this->sendInfoMessage(self::MSG_ROUTINE_ALREADY_OPTED_OUT);
            $this->cancel($object_instance->getRefId());
            return;
        }

        $whitelist_entry = $whitelist_entry ??
            $this->repository->whitelist()->empty(
                $routine,
                $object_instance->getRefId(),
                $this->user->getId()
            );

        // store the existing or a new entry with the updated information.
        $this->repository->whitelist()->store(
            $whitelist_entry
                ->setUserId($this->user->getId())
                ->setOptOut(true)
        );

        // if a whitelist token was being used it must be redeemed.
        if (null !== $stored_token) {
            $this->repository->token()->redeem($stored_token);
        }

        $this->event_observer->broadcast(
            new RoutineEvent(
                $routine,
                $object_instance,
                RoutineEvent::EVENT_OPT_OUT
            )
        );

        // redirect back to the target object with according message.
        $this->sendSuccessMessage(self::MSG_ROUTINE_OPTED_OUT);
        $this->redirectToRefId($object_instance->getRefId());
    }

    /**
     * Undoes an opt-out if a valid whitelist entry exists for the requested
     * routine and object.
     *
     * When undoing an opt-out only the 'is_opt_out' attribute of the whitelist
     * entry will be adjusted, so the entry would fall back to (maybe) a
     * previous elongation.
     */
    protected function undoOptOut(): void
    {
        // abort if the requested object could not be retrieved.
        if (null === $this->object_ref_id ||
            null === ($object_instance = $this->getObjectInstance($this->object_ref_id))
        ) {
            $this->sendErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            $this->cancel();
            return;
        }

        // abort if the requested routine could not be retrieved.
        if (null === $this->routine->getRoutineId()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_NOT_FOUND);
            $this->cancel();
            return;
        }

        $whitelist_entry = $this->repository->whitelist()->get($this->routine, $object_instance->getRefId());

        // abort if the current object is not whitelisted as opt-out.
        if (null === $whitelist_entry || !$whitelist_entry->isOptOut()) {
            $this->sendErrorMessage(self::MSG_ROUTINE_CANT_UNDO_OPT_OUT);
            $this->cancel();
            return;
        }

        $this->repository->whitelist()->store(
            $whitelist_entry
                ->setUserId($this->user->getId())
                ->setOptOut(false)
        );

        $this->sendSuccessMessage(self::MSG_ROUTINE_UNDID_OPT_OUT);
        $this->cancel();
    }

    /**
     * Helper function that either returns the object instance related to
     * the given token or the current request.
     *
     * @param IToken|null $token
     * @return ilObject|null
     */
    protected function getObjectByTokenOrRequest(IToken $token = null): ?ilObject
    {
        if (null !== $token) {
            return $this->getObjectInstance($token->getRefId());
        }

        if (null !== $this->object_ref_id) {
            return $this->getObjectInstance($this->object_ref_id);
        }

        return null;
    }

    /**
     * Helper function that either returns the object instance related to
     * the given token or the current request.
     *
     * @param IToken|null $token
     * @return IRoutine|null
     */
    protected function getRoutineByTokenOrRequest(IToken $token = null): ?IRoutine
    {
        if (null !== $token) {
            return $this->repository->routine()->get($token->getRoutineId());
        }

        if (null !== $this->routine->getRoutineId()) {
            return $this->routine;
        }

        return null;
    }

    /**
     * Tries to retrieve the ilObject instance of the given ref-id.
     *
     * @param int $ref_id
     * @return ilObject|null
     */
    protected function getObjectInstance(int $ref_id): ?ilObject
    {
        try {
            return ilObjectFactory::getInstanceByRefId($ref_id);
        } catch (Throwable $t) {
            return null;
        }
    }

    /**
     * Redirects to the requested object (ref-id) if it was provided as
     * query parameter, instead of the index page.
     *
     * Optionally, due to optOut() and postpone() having to use relative
     * object instances because they could be retrieved by tokens as well,
     * the ref-id can be provided to override the currently requested one.
     *
     * @inheritDoc
     */
    protected function cancel(?int $ref_id = null): void
    {
        $ref_id = $ref_id ?? $this->object_ref_id;

        if (null !== $ref_id) {
            $this->redirectToRefId($ref_id);
        }

        parent::cancel();
    }
}
