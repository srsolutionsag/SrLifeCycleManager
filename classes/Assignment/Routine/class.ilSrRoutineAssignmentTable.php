<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentTable extends ilSrAbstractAssignmentTable
{
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
        $this->addColumn($this->translator->txt(ilSrRoutineTable::COL_ROUTINE_TITLE));
        $this->addColumn($this->translator->txt(ilSrRoutineTable::COL_ROUTINE_USER_ID));
        $this->addColumn($this->translator->txt(ilSrRoutineTable::COL_ROUTINE_TYPE));
        $this->addColumn($this->translator->txt(ilSrRoutineTable::COL_ROUTINE_ELONGATION));
        $this->addColumn($this->translator->txt(ilSrRoutineTable::COL_ROUTINE_HAS_OPT_OUT));

        parent::addTableColumns();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        // if the 'owner_id' still exists, get the login-name.
        $owner_name = (ilObjUser::_exists($data[IRoutine::F_USER_ID])) ?
            (new ilObjUser((int) $data[IRoutine::F_USER_ID]))->getLogin() : ''
        ;

        // translate the routine type.
        $routine_type = $this->translator->txt($data[IRoutine::F_ROUTINE_TYPE]);

        // translate the status of 'opt_out_possible'.
        $status_opt_out = ($data[IRoutine::F_HAS_OPT_OUT]) ?
            $this->translator->txt(ilSrRoutineTable::STATUS_POSSIBLE) :
            $this->translator->txt(ilSrRoutineTable::STATUS_IMPOSSIBLE)
        ;

        $template->setVariable(ilSrRoutineTable::COL_ROUTINE_TITLE, $data[IRoutine::F_TITLE]);
        $template->setVariable(ilSrRoutineTable::COL_ROUTINE_USER_ID, $owner_name);
        $template->setVariable(ilSrRoutineTable::COL_ROUTINE_TYPE, $routine_type);
        $template->setVariable(ilSrRoutineTable::COL_ROUTINE_ELONGATION, $data[IRoutine::F_ELONGATION]);
        $template->setVariable(ilSrRoutineTable::COL_ROUTINE_HAS_OPT_OUT, $status_opt_out);

        parent::renderTableRow($template, $data);
    }

    /**
     * @inheritDoc
     */
    protected function getDropdownActions(array $data) : array
    {
        $actions = $this->getDefaultActions();

        // add the delete action if the requested object is directly assigned
        // to the routine (and not by recursion), otherwise the whitelist
        // actions might be added (see below).
        if ($this->parent_obj->getAssignmentRefId() === (int) $data[IRoutineAssignment::F_REF_ID]) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ASSIGNMENT_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    get_class($this->parent_obj),
                    ilSrAbstractAssignmentGUI::CMD_ASSIGNMENT_DELETE
                )
            );

            return $actions;
        }

        return $actions;
    }

    /**
     * @inheritDoc
     */
    protected function setActionParameters(int $routine_id, int $ref_id) : void
    {
        parent::setActionParameters($routine_id, $ref_id);

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
    }
}