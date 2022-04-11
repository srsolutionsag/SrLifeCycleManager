<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentObjectTable extends ilSrAbstractTable
{
    use ilSrRoutineAssignmentTableHelper;

    // ilSrRoutineAssignmentObjectTable table columns:
    protected const COL_ASSIGNED_REF_ID = 'col_routine_assignment_ref_id';
    protected const COL_OBJECT_TITLE = 'col_routine_assignment_obj_title';
    protected const COL_IS_RECURSIVE = 'col_routine_assignment_recursive';
    protected const COL_IS_ACTIVE = 'col_routine_assignment_active';

    // ilSrRoutineAssignmentObjectTable language variables:
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
        return 'tpl.routine_assignment_object_table_row.html';
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
                $this->getAssignmentActionDropdown(
                    (int) $data[IRoutineAssignment::F_ROUTINE_ID],
                    (int) $data[IRoutineAssignment::F_REF_ID]
                )
            )
        );
    }
}