<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;

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
    protected function getTemplateName(): string
    {
        return 'tpl.object_assignment_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_REF_ID));
        $this->addColumn($this->translator->txt(self::COL_OBJECT_TITLE));

        parent::addTableColumns();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data): void
    {
        $object_title = ilObject2::_lookupTitle(
            ilObject2::_lookupObjectId((int) $data[IRoutineAssignment::F_REF_ID])
        );

        $template->setVariable(self::COL_ASSIGNMENT_REF_ID, $data[IRoutineAssignment::F_REF_ID]);
        $template->setVariable(self::COL_OBJECT_TITLE, $object_title);

        parent::renderTableRow($template, $data);
    }

    /**
     * @inheritDoc
     */
    protected function getDropdownActions(array $data): array
    {
        $actions = $this->getDefaultActions();

        // the delete action can always be shown, since the table will be
        // displayed for routine managers.
        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_DELETE),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_gui),
                ilSrAbstractAssignmentGUI::CMD_ASSIGNMENT_DELETE
            )
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_OBJECT_VIEW),
            ilLink::_getLink((int) $data[IRoutineAssignment::F_REF_ID])
        );

        return $actions;
    }
}
