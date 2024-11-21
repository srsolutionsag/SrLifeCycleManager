<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\AssignmentFormProcessor;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * This class holds the similarities of both, routine and object assignment
 * GUIs and is used as an abstract base class for them.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The wording might be kind of confusing (I have to admit), but I figured once
 * I write this down it seems relatable.
 *
 * The derived class @see ilSrRoutineAssignmentGUI and everything other which
 * is named in that fashion, is used for assigning multiple ROUTINES to one object.
 * The derived class @see ilSrObjectAssignmentGUI on the other hand is used to
 * assign multiple OBJECTS to one routine.
 *
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractAssignmentGUI extends ilSrAbstractGUI
{
    // ilSrAssignmentGUI command names:
    public const CMD_ASSIGNMENT_SAVE = 'save';
    public const CMD_ASSIGNMENT_EDIT = 'edit';
    public const CMD_ASSIGNMENT_DELETE = 'delete';

    // ilSrAssignmentGUI language variables:
    protected const MSG_ASSIGNMENT_SUCCESS = 'msg_assignment_success';
    protected const MSG_ASSIGNMENT_FAILURE = 'msg_assignment_failure';
    protected const PAGE_TITLE = 'page_title_routine_assignment';

    /**
     * @var int|null
     */
    protected $assignment_ref_id;

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * Tries to retrieve an existing assignment for the requested object-
     * ref-id and routine-id.
     *
     * If no assignment is found, an empty assignment is created and it's
     * properties are set to the requested ids.
     *
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->assignment_ref_id = $this->getRequestedAssignmentRefId();
        $this->assignment =
            $this->getRequestedAssignment() ??
            $this->repository->assignment()->empty($this->user->getId())
        ;

        $this->assignment
            ->setRoutineId($this->routine->getRoutineId())
            ->setRefId($this->assignment_ref_id)
        ;
    }

    /**
     * Public getter is due to actions in @see ilSrAbstractAssignmentTable::getActionDropdown().
     *
     * @return int|null
     */
    public function getAssignmentRefId(): ?int
    {
        return $this->assignment_ref_id;
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void
    {
        $template->setTitle($this->translator->txt(self::PAGE_TITLE));

        $tabs->addBackToIndex(static::class);

        // only add tabs in administration context.
        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION === $this->origin) {
            $tabs
                ->addConfigurationTab()
                ->addRoutineTab()
                ->addPreviewTab()
                ->deactivateTabs()
            ;
        }
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        // all actions are available for routine- or assignment-managers.
        return (
            $access_handler->canManageRoutines() ||
            $access_handler->canManageAssignments()
        );
    }

    /**
     * Displays an assignment form on the current page, which is delivered
     * by the derived class.
     */
    protected function edit(): void
    {
        $this->render($this->getForm());
    }

    /**
     * Processes the submitted assignment-form data.
     *
     * If the data is valid, the user is redirected back to
     * @see ilSrAbstractAssignmentGUI::index().
     *
     * If the data is invalid, the processed form including
     * the error messages is shown.
     */
    protected function save(): void
    {
        $processor = new AssignmentFormProcessor(
            $this->repository->assignment(),
            $this->assignment,
            $this->request,
            $this->getForm()
        );

        if ($processor->processForm()) {
            $this->sendSuccessMessage(self::MSG_ASSIGNMENT_SUCCESS);
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * Deletes an existing assignment from the database, if the routine- and ref-id
     * were provided.
     *
     * Note that this method must only be called if the assignment-ref-id is equal
     * to the current ref-id OR the user is within administration context.
     */
    protected function delete(): void
    {
        if (null === $this->assignment->getRoutineId() || null === $this->assignment->getRefId()) {
            throw new LogicException("Cannot delete assignment without routine- and ref-id.");
        }

        if (IRoutine::ORIGIN_TYPE_ADMINISTRATION === $this->origin ||
            $this->object_ref_id === $this->assignment->getRefId()
        ) {
            $this->repository->assignment()->delete($this->assignment);
            $this->sendSuccessMessage(self::MSG_ASSIGNMENT_SUCCESS);
        } else {
            $this->sendErrorMessage(self::MSG_ASSIGNMENT_FAILURE);
        }

        $this->cancel();
    }

    /**
     * Redirects back to the current object if one has been provided.
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

    /**
     * Fetches an assignment from the database for the requested routine
     * and object (ref-id) if it exists.
     *
     * @return IRoutineAssignment|null
     */
    protected function getRequestedAssignment(): ?IRoutineAssignment
    {
        $assigned_ref_id = $this->getRequestedAssignmentRefId();
        $routine_id = $this->routine->getRoutineId();

        if (null !== $routine_id && null !== $assigned_ref_id) {
            return $this->repository->assignment()->get($routine_id, $assigned_ref_id);
        }

        return null;
    }

    /**
     * Returns the requested ref-id that should be used for the current assignment.
     *
     * @return int|null
     */
    protected function getRequestedAssignmentRefId(): ?int
    {
        $assignment_ref_id = $this->getRequestParameter($this->getAssignmentRefIdParameter());
        if (null !== $assignment_ref_id) {
            return (int) $assignment_ref_id;
        }

        return null;
    }

    /**
     * This method MUST return the query-parameter name that is expected to
     * deliver the ref-id of the current assignment.
     *
     * The method is also public, so that @see ilSrAbstractAssignmentTable can
     * retrieve the assignment GUIs proper action-parameter.
     *
     * @return string
     */
    abstract public function getAssignmentRefIdParameter(): string;

    /**
     * This method MUST return the form instance of the derived class, which
     * should be used in @see ilSrAbstractAssignmentGUI::save().
     *
     * @return Form
     */
    abstract protected function getForm(): Form;
}
