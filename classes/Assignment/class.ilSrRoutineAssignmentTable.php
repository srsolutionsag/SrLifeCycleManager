<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentTable extends ilSrAbstractTable
{
    // ilSrRoutineAssignmentTable table columns:
    protected const COL_ASSIGNED_REF_ID = 'col_routine_assignment_ref_id';
    protected const COL_OBJECT_TITLE = 'col_routine_assignment_obj_title';
    protected const COL_IS_RECURSIVE = 'col_routine_assignment_recursive';
    protected const COL_IS_ACTIVE = 'col_routine_assignment_active';

    // ilSrRoutineAssignmentTable language variables:
    protected const STATUS_ACTIVE = 'status_active';
    protected const STATUS_INACTIVE = 'status_inactive';
    protected const STATUS_RECURSIVE = 'status_recursive';
    protected const STATUS_NOT_RECURSIVE = 'status_not_recursive';
    protected const ACTION_ASSIGNMENT_EDIT = 'action_routine_assignment_edit';
    protected const ACTION_ASSIGNMENT_DELETE = 'action_routine_assignment_delete';

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.routine_assignment_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_ASSIGNED_REF_ID));
        $this->addColumn($this->translator->txt(self::COL_OBJECT_TITLE));
        $this->addColumn($this->translator->txt(self::COL_IS_RECURSIVE));
        $this->addColumn($this->translator->txt(self::COL_IS_ACTIVE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        // translate the status of 'active'.
        $status_active = ($data[IRoutineAssignment::F_IS_ACTIVE]) ?
            $this->translator->txt(self::STATUS_ACTIVE) :
            $this->translator->txt(self::STATUS_INACTIVE)
        ;

        $status_recursive = ($data[IRoutineAssignment::F_RECURSIVE]) ?
            $this->translator->txt(self::STATUS_RECURSIVE) :
            $this->translator->txt(self::STATUS_NOT_RECURSIVE)
        ;

        $object_title = ilObject2::_lookupTitle(
            ilObject2::_lookupObjectId((int) $data[IRoutineAssignment::F_REF_ID])
        );

        $template->setVariable(self::COL_ASSIGNED_REF_ID, $data[IRoutineAssignment::F_REF_ID]);
        $template->setVariable(self::COL_OBJECT_TITLE, $object_title);
        $template->setVariable(self::COL_IS_RECURSIVE, $status_recursive);
        $template->setVariable(self::COL_IS_ACTIVE, $status_active);

        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->getActionDropdown(
                    (int) $data[IRoutineAssignment::F_ROUTINE_ID],
                    (int) $data[IRoutineAssignment::F_REF_ID]
                )
            )
        );
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $routine_id, int $ref_id) : Dropdown
    {
        $this->setActionParameters($routine_id, $ref_id);

        $actions[self::ACTION_ASSIGNMENT_EDIT] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_EDIT),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineAssignmentGUI::class,
                ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        $actions[self::ACTION_ASSIGNMENT_DELETE] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_DELETE),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineAssignmentGUI::class,
                ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return void
     */
    protected function setActionParameters(int $routine_id, int $ref_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID,
            $ref_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ASSIGNED_REF_ID,
            $ref_id
        );
    }
}