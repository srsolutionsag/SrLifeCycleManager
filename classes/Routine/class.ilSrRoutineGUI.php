<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormProcessor;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This GUI class is responsible for all actions regarding routines.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineGUI extends ilSrAbstractGUI
{
    // ilSrRoutineGUI command/method names:
    public const CMD_ROUTINE_EDIT    = 'edit';
    public const CMD_ROUTINE_SAVE    = 'save';
    public const CMD_ROUTINE_DELETE  = 'delete';
    public const CMD_ROUTINE_OPT_OUT = 'optOut';
    public const CMD_ROUTINE_EXTEND  = 'extend';

    // ilSrRoutineGUI language variables:
    protected const MSG_ROUTINE_SUCCESS = 'msg_routine_success';
    protected const MSG_ROUTINE_ERROR = 'msg_routine_error';
    protected const MSG_ROUTINE_CANT_EXTEND = 'msg_routine_cant_extend';
    protected const MSG_ROUTINE_EXTENDED = 'msg_routine_extended';
    protected const MSG_ROUTINE_EXTEND_ERROR = 'msg_routine_extend_error';
    protected const MSG_ROUTINE_CANT_OPT_OUT = 'msg_routine_cant_opt_out';
    protected const MSG_ROUTINE_OPTED_OUT = 'msg_routine_opted_out';
    protected const MSG_ROUTINE_OPT_OUT_ERROR = 'msg_routine_opt_out_error';
    protected const PAGE_TITLE = 'page_title_routine';

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * Initializes the routine form-builder and fetches the required request
     * query parameters.
     */
    public function __construct()
    {
        parent::__construct();

        $this->form_builder = new RoutineFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->routine ?? $this->repository->routine()->empty($this->user->getId(), $this->origin),
            $this->getFormAction()
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs) : void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));

        // don't override the "back-to-plugins" target in configuration.
        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION !== $this->origin) {
            $tabs->setBackToTarget($this->getBackToTarget());
        }

        $tabs
            ->addConfigurationTab()
            ->addRoutineTab(true)
        ;
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        switch ($command) {
            // routines must be visible for object administrators.
            case self::CMD_INDEX:
                return $access_handler->canViewRoutines($this->object_ref_id);

            // routine extension or opt-out must only be accessible for
            // administrators of the requested object.
            case self::CMD_ROUTINE_EXTEND:
            case self::CMD_ROUTINE_OPT_OUT:
                if (null === $this->object_ref_id) {
                    return false;
                }
                return $access_handler->isAdministratorOf($this->object_ref_id);

            // for all other commands the user must be able to manage routines.
            default:
                return $access_handler->canManageRoutines();
        }
    }

    /**
     * Displays all routines that affect the requested routine-ref-id.
     *
     * Affected means, that the id itself belongs to a routine or one
     * of its parents does.
     */
    protected function index() : void
    {
        $table = new ilSrRoutineTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->routine()->getAllByRefId(
                $this->object_ref_id ?? 1,
                true
            )
        );

        $this->toolbar_manager->addRoutineToolbar();
        $this->render($table->getTable());
    }

    /**
     * Displays the routine form on the current page.
     *
     * If a routine is requested, the form-builder already was initialized
     * with the according data, therefore this method can be used for
     * create AND update commands.
     */
    protected function edit() : void
    {
        // if the current routine has not been stored yet and
        // an object was requested, the user wants to create
        // a routine at this position, therefore the object is
        // treated as routine-ref-id.
        if (null !== $this->object_ref_id &&
            null === $this->routine->getRoutineId()
        ) {
            $this->routine->setRefId($this->object_ref_id);
        }

        $this->render($this->form_builder->getForm());
    }

    /**
     * Processes the submitted routine-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrRoutineGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    protected function save() : void
    {
        $processor = new RoutineFormProcessor(
            $this->repository->routine(),
            $this->request,
            $this->form_builder->getForm(),
            $this->routine
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_ROUTINE_SUCCESS);
            $this->cancel();
        }

        $this->displayErrorMessage(self::MSG_ROUTINE_ERROR);
        $this->render($processor->getProcessedForm());
    }

    /**
     * Deletes the requested routine and redirects the user back to
     * @see ilSrRoutineGUI::index().
     */
    protected function delete() : void
    {
        if (null !== $this->routine->getRoutineId()) {
            $this->sendSuccessMessage(self::MSG_ROUTINE_SUCCESS);
            $this->repository->routine()->delete($this->routine);
        } else {
            $this->sendErrorMessage(self::MSG_ROUTINE_ERROR);
        }

        $this->cancel();
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
            $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            return;
        }

        // abort if the requested routine does not support elongations.
        if (1 > $this->routine->getElongation()) {
            $this->displayErrorMessage(self::MSG_ROUTINE_CANT_EXTEND);
            return;
        }

        $success = $this->repository->routine()->whitelist()->extendObjectByRefId(
            $this->routine,
            $this->object_ref_id
        );

        if ($success) {
            $this->sendSuccessMessage(self::MSG_ROUTINE_EXTENDED);
        } else {
            $this->sendErrorMessage(self::MSG_ROUTINE_EXTEND_ERROR);
        }

        // redirect back to the target object with according message.
        $this->ctrl->redirectToURL(ilLink::_getLink($this->object_ref_id));
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
            $this->displayErrorMessage(self::MSG_OBJECT_NOT_FOUND);
            return;
        }

        // abort if the requested routine does not support elongations.
        if (!$this->routine->hasOptOut()) {
            $this->displayErrorMessage(self::MSG_ROUTINE_CANT_OPT_OUT);
            return;
        }

        $success = $this->repository->routine()->whitelist()->optOutObjectByRefId(
            $this->routine,
            $this->object_ref_id
        );

        if ($success) {
            $this->sendSuccessMessage(self::MSG_ROUTINE_OPTED_OUT);
        } else {
            $this->sendErrorMessage(self::MSG_ROUTINE_OPT_OUT_ERROR);
        }

        // redirect back to the target object with according message.
        $this->ctrl->redirectToURL(ilLink::_getLink($this->object_ref_id));
    }

    /**
     * Returns a back-to target to either the requested object, if provided,
     * or the routine GUI index.
     *
     * @return string
     */
    protected function getBackToTarget() : string
    {
        if (null !== $this->object_ref_id) {
            return ilLink::_getLink($this->object_ref_id);
        }

        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_INDEX
        );
    }

    /**
     * Returns the routine form-action pointing to @see ilSrRoutineGUI::save().
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_ROUTINE_SAVE
        );
    }
}