<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrObjectAssignmentTable extends ilSrAbstractAssignmentTable
{
    // ilSrRoutineAssignmentObjectTable table columns:
    protected const COL_ASSIGNMENT_REF_ID = 'col_object_assignment_ref_id';
    protected const COL_OBJECT_TITLE = 'col_object_assignment_title';

    // ilSrRoutineAssignmentObjectTable language variables:
    protected const ACTION_OBJECT_VIEW = 'action_object_view';

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.object_assignment_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_REF_ID));
        $this->addColumn($this->translator->txt(self::COL_OBJECT_TITLE));

        parent::addTableColumns();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        $object_title = ilObject2::_lookupTitle(
            ilObject2::_lookupObjectId((int) $data[IRoutineAssignment::F_REF_ID])
        );

        $template->setVariable(self::COL_ASSIGNMENT_REF_ID, $data[IRoutineAssignment::F_REF_ID]);
        $template->setVariable(self::COL_OBJECT_TITLE, $object_title);

        parent::renderTableRow($template, $data);
    }

    /**
     * Override parent dropdown to add 'view object' action as well.
     *
     * @inheritDoc
     */
    protected function getActionDropdown(int $routine_id, int $ref_id) : Dropdown
    {
        $dropdown = parent::getActionDropdown($routine_id, $ref_id);
        $actions  = $dropdown->getItems();

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_OBJECT_VIEW),
            ilLink::_getLink($ref_id)
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }
}