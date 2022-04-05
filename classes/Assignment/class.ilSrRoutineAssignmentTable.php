<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentTable extends ilSrAbstractTable
{
    protected const COL_ASSIGNED_REF_ID = 'col_routine_assignment_ref_id';
    protected const COL_OBJECT_TITLE = 'col_routine_assignment_obj_title';
    protected const COL_IS_RECURSIVE = 'col_routine_assignment_recursive';
    protected const COL_IS_ACTIVE = 'col_routine_assignment_active';

    protected const STATUS_ACTIVE = 'status_active';
    protected const STATUS_INACTIVE = 'status_inactive';

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


    }
}