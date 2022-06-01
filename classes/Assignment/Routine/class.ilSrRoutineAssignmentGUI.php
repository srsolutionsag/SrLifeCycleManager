<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\RoutineAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * This class is responsible for assigning multiple or one routine to
 * exactly one object.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The GUI must therefore be provided with @see ilSrAbstractGUI::PARAM_OBJECT_REF_ID,
 * Otherwise the constructor will throw an exception.
 * Optionally, @see ilSrAbstractGUI::PARAM_ROUTINE_ID can be provided, which means
 * an existing assignment is edited.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentGUI extends ilSrAbstractAssignmentGUI
{
    // ilSrRoutineAssignmentGUI language variables:
    protected const MSG_NO_UNASSIGNED_ROUTINES = 'msg_no_unassigned_routines';

    /**
     * @var IRoutine[]
     */
    protected $unassigned_routines;

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * Throws an exception if the request doesn't provide an object (ref-id).
     *
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->panicOnMissingAssignmentRefId();

        $this->unassigned_routines = $this->repository->routine()->getAllUnassigned($this->assignment_ref_id);
        $this->form_builder = new RoutineAssignmentFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->assignment,
            $this->repository->routine()->getAll(),
            $this->unassigned_routines,
            $this->getFormAction(
                self::CMD_ASSIGNMENT_SAVE,
                self::PARAM_ROUTINE_ID
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getAssignmentRefIdParameter() : string
    {
        return self::PARAM_OBJECT_REF_ID;
    }

    /**
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
            $this->repository->assignment()->getAllWithJoinedDataByRefId($this->assignment_ref_id)
        );

        $this->tab_manager->addBackToObject($this->assignment_ref_id);
        $this->toolbar_manager->addRoutineAssignmentToolbar();
        $this->render($table->getTable());
    }

    /**
     * Override parent method to only show the assignment form, if there
     * are unassigned routines for the requested object.
     *
     * If an assignment is edited, the form must still be displayed though.
     *
     * @inheritDoc
     */
    protected function edit() : void
    {
        if (!empty($this->unassigned_routines) ||
            (null !== $this->assignment->getRefId() && null !== $this->assignment->getRoutineId())
        ) {
            parent::edit();
        } else {
            $this->displayInfoMessage(self::MSG_NO_UNASSIGNED_ROUTINES);
        }
    }

    /**
     * Throws an exception if the request didn't provide an object-ref-id.
     *
     * @see ilSrAbstractAssignmentGUI::PARAM_OBJECT_REF_ID
     * @throws LogicException
     */
    protected function panicOnMissingAssignmentRefId() : void
    {
        if (null === $this->assignment_ref_id) {
            throw new LogicException(self::class . " must be provided with an object (ref-id).");
        }
    }

    /**
     * @inheritDoc
     */
    protected function getForm() : Form
    {
        return $this->form_builder->getForm();
    }
}