<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormProcessor;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentGUI extends ilSrAbstractGUI
{
    // ilSrRoutineAssignmentGUI command/method names:
    public const CMD_ASSIGNMENT_EDIT = 'edit';
    public const CMD_ASSIGNMENT_SAVE = 'save';
    public const CMD_ASSIGNMENT_DELETE = 'delete';

    // ilSrRoutineAssignmentGUI language variables:
    protected const MSG_ASSIGNMENT_SUCCESS = 'msg_assignment_success';
    protected const MSG_ASSIGNMENT_ERROR = 'msg_assignment_error';
    protected const PAGE_TITLE = 'page_title_routine_assignment';

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * Initializes the assignment form-builder and fetches an active
     * assignment if possible.
     *
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->panicOnMissingRoutine();

        $this->assignment =
            $this->getRequestedAssignment() ??
            $this->repository->assignment()->empty($this->routine)
        ;

        $this->form_builder = new RoutineAssignmentFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->repository->assignment()->empty($this->routine),
            $this->repository->routine(),
            $this->getFormAction()
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
            ->addRoutineTab()
            ->deactivateTabs()
            ->setBackToTarget(
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineGUI::class,
                    self::CMD_INDEX
                )
            )
        ;
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command) : bool
    {
        // user must be privileged for all actions concerning assignments.
        return $access_handler->canManageAssignments();
    }

    /**
     * Displays all existing assignments that are related to the requested
     * routine on the current page.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $table = new ilSrRoutineAssignmentTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->assignment()->getByRoutine($this->routine, true)
        );

        $this->toolbar_manager->addAssignmentToolbar();
        $this->render($table->getTable());
    }

    /**
     * Displays a form for assigning objects to the requested routine.
     *
     * If an assignment exists already, the form-builder already was initialized
     * with the according data, therefore this method can be used for
     * both create AND update commands.
     */
    protected function edit() : void
    {
        $this->render($this->form_builder->getForm());
    }

    /**
     * Processes the submitted assignment-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrRoutineAssignmentGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    protected function save() : void
    {
        $processor = new RoutineAssignmentFormProcessor(
            $this->repository->assignment(),
            $this->assignment,
            $this->request,
            $this->form_builder->getForm()
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_ASSIGNMENT_SUCCESS);
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * Fetches an assignment from the database for the requested routine
     * and object (ref-id) if it exists.
     *
     * @return IRoutineAssignment|null
     */
    protected function getRequestedAssignment() : ?IRoutineAssignment
    {
        $routine_id = $this->routine->getRoutineId();
        $ref_id = $this->object_ref_id;

        if (null !== $routine_id && null !== $ref_id) {
            return $this->repository->assignment()->get($this->routine, $ref_id);
        }

        return null;
    }

    /**
     * Returns the assignment form-action pointing to @see ilSrRoutineAssignmentGUI::save().
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        return $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_ASSIGNMENT_SAVE
        );
    }
}