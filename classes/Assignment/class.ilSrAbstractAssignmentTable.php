<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Button\Button;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractAssignmentTable extends ilSrAbstractTable
{
    // ilSrRoutineAssignmentTable column names:
    protected const COL_ASSIGNMENT_RECURSIVE = 'col_assignment_recursive';
    protected const COL_ASSIGNMENT_OWNER = 'col_assignment_user_id';
    protected const COL_ASSIGNMENT_ACTIVE = 'col_assignment_active';

    // ilSrAbstractAssignmentTable action names:
    protected const ACTION_ASSIGNMENT_EDIT = 'action_routine_assignment_edit';
    protected const ACTION_ASSIGNMENT_DELETE = 'action_routine_assignment_delete';
    protected const ACTION_WHITELIST_POSTPONE = 'action_whitelist_postpone';
    protected const ACTION_WHITELIST_OPT_OUT = 'action_whitelist_opt_out';

    // ilSrRoutineAssignmentTable language variables:
    protected const STATUS_RECURSIVE = 'status_recursive';
    protected const STATUS_NOT_RECURSIVE = 'status_not_recursive';
    protected const STATUS_ACTIVE = 'status_active';
    protected const STATUS_INACTIVE = 'status_inactive';

    /**
     * @inheritDoc
     */
    protected function addTableColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_OWNER));
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_RECURSIVE));
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_ACTIVE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data): void
    {
        // translate the status of 'recursive'.
        $status_recursive = ($data[IRoutineAssignment::F_RECURSIVE]) ?
            $this->translator->txt(self::STATUS_RECURSIVE) :
            $this->translator->txt(self::STATUS_NOT_RECURSIVE);

        // translate the status of 'active'.
        $status_active = ($data[IRoutineAssignment::F_IS_ACTIVE]) ?
            $this->translator->txt(self::STATUS_ACTIVE) :
            $this->translator->txt(self::STATUS_INACTIVE);

        // if the 'usr_id' still exists, get the login-name.
        $owner_name = (ilObjUser::_exists((int)$data[IRoutineAssignment::F_USER_ID])) ?
            (new ilObjUser((int) $data[IRoutineAssignment::F_USER_ID]))->getLogin() : '';

        $template->setVariable(self::COL_ASSIGNMENT_OWNER, $owner_name);
        $template->setVariable(self::COL_ASSIGNMENT_RECURSIVE, $status_recursive);
        $template->setVariable(self::COL_ASSIGNMENT_ACTIVE, $status_active);

        $this->setActionParameters(
            (int) $data[IRoutineAssignment::F_ROUTINE_ID],
            (int) $data[IRoutineAssignment::F_REF_ID]
        );

        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->ui_factory->dropdown()->standard($this->getDropdownActions($data))
            )
        );
    }

    /**
     * @return Button[]
     */
    protected function getDefaultActions(): array
    {
        if (null === $this->parent_obj) {
            return [];
        }

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_EDIT),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_obj),
                ilSrAbstractAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        return $actions;
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     */
    protected function setActionParameters(int $routine_id, int $ref_id): void
    {
        $this->ctrl->setParameterByClass(
            get_class($this->parent_gui),
            ilSrAbstractAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            get_class($this->parent_gui),
            $this->parent_obj->getAssignmentRefIdParameter(),
            $ref_id
        );
    }

    /**
     * @param array $data
     * @return Button[]
     */
    abstract protected function getDropdownActions(array $data): array;
}
