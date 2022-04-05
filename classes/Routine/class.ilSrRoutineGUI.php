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

    // ilSrRoutineGUI language variables:
    protected const MSG_ROUTINE_SUCCESS = 'msg_routine_success';
    protected const MSG_ROUTINE_ERROR = 'msg_routine_error';
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