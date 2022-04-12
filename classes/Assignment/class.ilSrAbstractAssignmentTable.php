<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use ILIAS\UI\Component\Dropdown\Dropdown;

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

    // ilSrRoutineAssignmentTable language variables:
    protected const STATUS_RECURSIVE = 'status_recursive';
    protected const STATUS_NOT_RECURSIVE = 'status_not_recursive';
    protected const STATUS_ACTIVE = 'status_active';
    protected const STATUS_INACTIVE = 'status_inactive';

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_OWNER));
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_RECURSIVE));
        $this->addColumn($this->translator->txt(self::COL_ASSIGNMENT_ACTIVE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        // translate the status of 'recursive'.
        $status_recursive = ($data[IRoutineAssignment::F_RECURSIVE]) ?
            $this->translator->txt(self::STATUS_RECURSIVE) :
            $this->translator->txt(self::STATUS_NOT_RECURSIVE)
        ;

        // translate the status of 'active'.
        $status_active = ($data[IRoutineAssignment::F_IS_ACTIVE]) ?
            $this->translator->txt(self::STATUS_ACTIVE) :
            $this->translator->txt(self::STATUS_INACTIVE)
        ;

        // if the 'usr_id' still exists, get the login-name.
        $owner_name = (ilObjUser::_exists($data[IRoutineAssignment::F_USER_ID])) ?
            (new ilObjUser((int) $data[IRoutineAssignment::F_USER_ID]))->getLogin() : ''
        ;

        $template->setVariable(self::COL_ASSIGNMENT_OWNER, $owner_name);
        $template->setVariable(self::COL_ASSIGNMENT_RECURSIVE, $status_recursive);
        $template->setVariable(self::COL_ASSIGNMENT_ACTIVE, $status_active);

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

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_EDIT),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_obj),
                ilSrAbstractAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_DELETE),
            $this->ctrl->getLinkTargetByClass(
                get_class($this->parent_obj),
                ilSrAbstractAssignmentGUI::CMD_ASSIGNMENT_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
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
            get_class($this->parent_obj),
            ilSrAbstractAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            get_class($this->parent_obj),
            $this->parent_obj->getAssignmentRefIdParameter(),
            $ref_id
        );
    }
}