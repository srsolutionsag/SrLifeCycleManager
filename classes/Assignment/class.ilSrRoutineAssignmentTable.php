<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentTable extends ilSrRoutineTable
{
    use ilSrRoutineAssignmentTableHelper;

    // ilSrRoutineAssignmentTable table columns:
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
        return 'tpl.routine_assignment_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        // we cannot use parent::addTableColumns() because ilTable2GUI
        // considers the order of added columns strictly.
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TITLE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_USER_ID));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TYPE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_ELONGATION));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_HAS_OPT_OUT));
        $this->addColumn($this->translator->txt(self::COL_IS_RECURSIVE));
        $this->addColumn($this->translator->txt(self::COL_IS_ACTIVE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data, bool $add_actions = true) : void
    {
        parent::renderTableRow($template, $data, false);

        // translate the status of 'active'.
        $status_active = ($data[IRoutineAssignment::F_IS_ACTIVE]) ?
            $this->translator->txt(self::STATUS_ACTIVE) :
            $this->translator->txt(self::STATUS_INACTIVE)
        ;

        $status_recursive = ($data[IRoutineAssignment::F_RECURSIVE]) ?
            $this->translator->txt(self::STATUS_RECURSIVE) :
            $this->translator->txt(self::STATUS_NOT_RECURSIVE)
        ;

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