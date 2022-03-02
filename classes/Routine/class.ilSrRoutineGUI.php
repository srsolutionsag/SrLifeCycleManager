<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormProcessor;

/**
 * This GUI class is responsible for all actions regarding routines.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineGUI extends ilSrAbstractGUI
{
    // ilSrRoutineGUI GET-parameter names:
    public const PARAM_OBJECT_REF_ID = 'ref_id';

    // ilSrRoutineGUI command/method names:
    public const CMD_ROUTINE_EDIT    = 'edit';
    public const CMD_ROUTINE_SAVE    = 'save';
    public const CMD_ROUTINE_DELETE  = 'delete';
    public const CMD_ROUTINE_OPT_OUT = 'optOut';
    public const CMD_ROUTINE_EXTEND  = 'extend';

    // ilSrRoutineGUI language variables:
    protected const MSG_ROUTINE_SUCCESS = 'msg_routine_success';
    protected const MSG_ROUTINE_ERROR = 'msg_routine_error';
    protected const PAGE_TITLE = 'page_title_routine';

    /**
     * @var int|null
     */
    protected $object_ref_id;

    /**
     * @var int|null
     */
    protected $routine_ref_id;

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

        $this->routine_ref_id = ($id = $this->getRequestParameter(self::PARAM_ROUTINE_REF_ID)) ? (int) $id : $id;
        $this->object_ref_id = ($id = $this->getRequestParameter(self::PARAM_OBJECT_REF_ID)) ? (int) $id : $id;

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
        if (null !== $this->routine_ref_id) {
            $tabs->setBackToTarget(ilLink::_getLink($this->routine_ref_id));
        }

        $tabs->addConfigurationTab()->addRoutineTab(true);
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        switch ($command) {
            case self::CMD_INDEX:
                return $access_handler->canViewRoutines();

            case self::CMD_ROUTINE_EXTEND:
            case self::CMD_ROUTINE_OPT_OUT:
                if (null !== $this->object_ref_id) {
                    return $access_handler->isAdministratorOf((int) $this->object_ref_id);
                }
                return false;

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
            $this->repository->routine()->getAllByRefId($this->routine_ref_id ?? 1, true)
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
     * @return void
     */
    protected function extend() : void
    {

    }

    /**
     * @return void
     */
    protected function optOut() : void
    {

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