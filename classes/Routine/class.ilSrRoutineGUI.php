<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Routine\RoutineFormProcessor;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Assignment\RoutineAssignment;

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
    protected $routine_form_builder;

    /**
     * Initializes the routine form-builder and fetches the required request
     * query parameters.
     */
    public function __construct()
    {
        parent::__construct();

        $this->routine_form_builder = new RoutineFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->routine,
            $this->getFormAction(
                self::CMD_ROUTINE_SAVE,
                self::PARAM_ROUTINE_ID
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs) : void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab()
            ->addRoutineTab(true)
            ->addPreviewTab()
        ;
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
     * Displays a table with all existing routines on the current page.
     */
    protected function index() : void
    {
        $table = new ilSrRoutineTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->repository->general(),
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->routine()->getAll(true)
        );

        // set back-to object target in repository context.
        if (IRoutine::ORIGIN_TYPE_REPOSITORY === $this->origin && null !== $this->object_ref_id) {
            $this->tab_manager->addBackToObject($this->object_ref_id);
        }

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
        // set back-to object target in repository context.
        if (IRoutine::ORIGIN_TYPE_REPOSITORY === $this->origin && null !== $this->object_ref_id) {
            $this->tab_manager->addBackToObject($this->object_ref_id);
        } else {
            $this->tab_manager->addBackToRoutines();
        }

        $this->render($this->routine_form_builder->getForm());
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
            $this->routine_form_builder->getForm(),
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
     * Redirects back to the requested object (ref-id) if one has
     * been provided.
     *
     * @inheritDoc
     */
    protected function cancel(): void
    {
        if (null !== $this->object_ref_id) {
            $this->redirectToRefId($this->object_ref_id);
        }

        parent::cancel();
    }

}
