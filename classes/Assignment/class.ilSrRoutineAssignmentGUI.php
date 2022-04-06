<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormProcessor;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormDirector;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentIntention;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\DI\HTTPServices;

/**
 * This class is responsible for assigning routines to objects and vise-versa.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentGUI extends ilSrAbstractGUI
{
    // ilSrRoutineAssignmentGUI command/method names:
    public const CMD_ASSIGNMENT_EDIT = 'edit';
    public const CMD_ASSIGNMENT_SAVE = 'save';
    public const CMD_ASSIGNMENT_DELETE = 'delete';
    public const CMD_OBJECT_SEARCH = 'searchObjects';

    // ilSrRoutineAssignmentGUI language variables:
    protected const MSG_ASSIGNMENT_SUCCESS = 'msg_assignment_success';
    protected const PAGE_TITLE = 'page_title_routine_assignment';

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @var RoutineAssignmentFormDirector
     */
    protected $form_director;

    /**
     * @var HTTPServices
     */
    protected $http;

    /**
     * Initializes the assignment form-builder and fetches an active
     * assignment if possible.
     *
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->http = $DIC->http();

        $this->assignment =
            ($this->getRequestedAssignment() ?? $this->repository->assignment()->empty())
                ->setRoutineId($this->routine->getRoutineId())
                ->setRefId($this->object_ref_id)
        ;

        $this->form_director = new RoutineAssignmentFormDirector(
            new RoutineAssignmentFormBuilder(
                $this->translator,
                $this->ui_factory->input()->container()->form(),
                $this->ui_factory->input()->field(),
                $this->refinery,
                $this->assignment,
                $this->repository->routine()->getAll(),
                $this->getFormAction(),
                $this->getAjaxAction()
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
            $this->getTableData()
        );

        $this->toolbar_manager->addAssignmentToolbar();
        $this->render($table->getTable());
    }

    /**
     * Displays a form to assign object(s) to routine(s).
     *
     * This method can be called in three different scenarios:
     *
     *      (a) the user want's to assign routines to an object, in this
     *          case the ref_id will be provided.
     *      (b) the user want's to assign objects to a routine, in this
     *          case the routine_id will be provided.
     *      (c) the user wants to adjust an existing assignment, in this
     *          case the routine_id AND ref_id will be provided.
     *
     * The method itself does not need to execute any checks, since the
     * form director will generate the form according to the assignment's
     * properties (e.g. the intention).
     */
    protected function edit() : void
    {
        $this->render($this->form_director->getFormByIntention($this->assignment));
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
            $this->form_director->getFormByIntention($this->assignment)
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_ASSIGNMENT_SUCCESS);
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * This method searches objects by the requested term and returns
     * them asynchronously (json-response).
     *
     * @see AbstractFormBuilder::getTagInputAutoCompleteBinder()
     */
    protected function searchObjects() : void
    {
        $body = $this->request->getQueryParams();
        $term = $body['term'] ?? '';

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody(Streams::ofString(json_encode(
                    $this->repository->ilias()->getObjectsByTerm($term)
                )))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
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
            return $this->repository->assignment()->get($routine_id, $ref_id);
        }

        return null;
    }

    /**
     * Returns the assignment table data according to the current assignment's intention.
     *
     * @return array
     */
    protected function getTableData() : array
    {
        switch ($this->assignment->getIntention()) {
            case IRoutineAssignmentIntention::OBJECT_ASSIGNMENT:
                return $this->repository->assignment()->getByRoutineId(
                    $this->assignment->getRoutineId(),
                    true
                );

            case IRoutineAssignmentIntention::ROUTINE_ASSIGNMENT:
                return $this->repository->assignment()->getByRefId(
                        $this->assignment->getRefId(),
                        true
                );

            default:
                return [];
        }
    }

    /**
     * Returns the ajax-action for @see AbstractFormBuilder::getTagInputAutoCompleteBinder().
     *
     * @return string
     */
    protected function getAjaxAction() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_OBJECT_SEARCH,
            '',
            true
        );
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