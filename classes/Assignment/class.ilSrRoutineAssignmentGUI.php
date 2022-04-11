<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormProcessor;
use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormDirector;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentIntention;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Component;
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
    // ilSrRoutineAssignmentGUI GET-parameter names:
    public const PARAM_ASSIGNED_REF_ID = 'assigned_ref_id';

    // ilSrRoutineAssignmentGUI command/method names:
    public const CMD_ASSIGNMENT_EDIT = 'edit';
    public const CMD_ASSIGNMENT_SAVE = 'save';
    public const CMD_ASSIGNMENT_DELETE = 'delete';
    public const CMD_OBJECT_SEARCH = 'searchObjects';

    // ilSrRoutineAssignmentGUI language variables:
    protected const MSG_ASSIGNMENT_SUCCESS = 'msg_assignment_success';
    protected const MSG_ASSIGNMENT_FAILURE = 'msg_assignment_failure';
    protected const PAGE_TITLE = 'page_title_routine_assignment';

    /**
     * @var int|null
     */
    protected $assigned_ref_id;

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
        $this->assigned_ref_id = $this->getRequestedAssignmentRefId();

        // try to fetch the assignment from the database or create an empty one.
        $this->assignment = $this->getRequestedAssignment() ?? $this->repository->assignment()->empty();
        // set the assignment properties that might have been submitted.
        $this->assignment
            ->setRoutineId($this->routine->getRoutineId())
            ->setRefId($this->assigned_ref_id)
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
            ),
            $this->repository->routine()->getAllUnassignedByRefId($this->assigned_ref_id ?? 1)
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
                    self::class,
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
        // all actions are available for routine- or assignment-managers.
        return (
            $access_handler->canManageRoutines() ||
            $access_handler->canManageAssignments()
        );
    }

    /**
     * Displays all existing assignments that are related to the requested
     * routine on the current page.
     *
     * @inheritDoc
     */
    protected function index() : void
    {
        $this->tab_manager->setBackToTarget($this->getBackToTarget());
        $this->toolbar_manager->addAssignmentToolbar();
        $this->render($this->getTable());
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
        // overrides the back-to target while in form context to
        // get back to the overview.
        $this->tab_manager->setBackToTarget(
            $this->ctrl->getLinkTargetByClass(
                self::class,
                self::CMD_INDEX
            )
        );

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
     * Deletes the requested routine and redirects the user back to
     * @see ilSrRoutineGUI::index().
     */
    protected function delete() : void
    {
        // only delete assignments that are directly assigned to a routine,
        // otherwise whitelist entries must be made.
        if (IRoutineAssignmentIntention::EDIT_ASSIGNMENT !== $this->assignment->getIntention() ||
            $this->assignment->getRefId() !== $this->assigned_ref_id
        ) {
            $this->sendErrorMessage(self::MSG_ASSIGNMENT_FAILURE);
            $this->cancel();
        }

        $this->repository->assignment()->delete($this->assignment);

        $this->sendSuccessMessage(self::MSG_ASSIGNMENT_SUCCESS);
        $this->cancel();
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
                    $this->repository->general()->getObjectsByTerm($term)
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
        if (null !== $routine_id && null !== $this->assigned_ref_id) {
            return $this->repository->assignment()->get($routine_id, $this->assigned_ref_id);
        }

        return null;
    }

    /**
     * Returns the requested assignment object ref-id if it was provided.
     *
     * @return int|null
     */
    protected function getRequestedAssignmentRefId() : ?int
    {
        $assigned_ref_id = $this->getRequestParameter(self::PARAM_ASSIGNED_REF_ID);
        if (null !== $assigned_ref_id) {
            return (int) $assigned_ref_id;
        }

        return null;
    }

    /**
     * @return Component
     */
    protected function getTable() : Component
    {
        switch ($this->assignment->getIntention()) {
            case IRoutineAssignmentIntention::OBJECT_ASSIGNMENT:
                return (new ilSrRoutineAssignmentObjectTable(
                    $this->ui_factory,
                    $this->renderer,
                    $this->translator,
                    $this->access_handler,
                    $this->ctrl,
                    $this,
                    self::CMD_INDEX,
                    $this->repository->assignment()->getAllByRoutineId(
                        $this->assignment->getRoutineId(),
                        true
                    )
                ))->getTable();

            case IRoutineAssignmentIntention::ROUTINE_ASSIGNMENT:
                return (new ilSrRoutineAssignmentTable(
                    $this->ui_factory,
                    $this->renderer,
                    $this->translator,
                    $this->access_handler,
                    $this->ctrl,
                    $this,
                    self::CMD_INDEX,
                    $this->repository->assignment()->getAllWithJoinedDataByRefId(
                        $this->assignment->getRefId()
                    )
                ))->getTable();

            default:
                throw new LogicException("Cannot gather assignments without routine or object (ref-id).");
        }
    }

    /**
     * Returns the assignment form-action pointing to @see ilSrRoutineAssignmentGUI::save().
     *
     * @return string
     */
    protected function getFormAction() : string
    {
        // note that when the ref-id is null, ilCtrl won't append
        // the parameter string to the generated link targets.
        $this->ctrl->setParameterByClass(
            self::class,
            self::PARAM_ASSIGNED_REF_ID,
            $this->assigned_ref_id
        );

        $action = $this->ctrl->getFormActionByClass(
            self::class,
            self::CMD_ASSIGNMENT_SAVE
        );

        // remove the ref-id parameter, so that $this->cancel() will
        // not remember the assignment and mistake the intention.
        if (IRoutineAssignmentIntention::ROUTINE_ASSIGNMENT !== $this->assignment->getIntention()) {
            $this->ctrl->clearParameterByClass(
                self::class,
                self::PARAM_ASSIGNED_REF_ID
            );
        }

        return $action;
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
     * Returns the tabs back-to target according to the current intention.
     *
     * @return string
     */
    protected function getBackToTarget() : string
    {
        if (IRoutineAssignmentIntention::ROUTINE_ASSIGNMENT === $this->assignment->getIntention()) {
            return ilLink::_getLink($this->assigned_ref_id);
        }

        return $this->ctrl->getLinkTargetByClass(
            ilSrRoutineGUI::class,
            self::CMD_INDEX
        );
    }
}