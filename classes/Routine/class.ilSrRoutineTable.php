<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineTable extends ilSrAbstractTable
{
    // ilSrRoutineTable table columns:
    public const COL_ROUTINE_ELONGATION = 'col_routine_elongation';
    public const COL_ROUTINE_HAS_OPT_OUT = 'col_routine_has_opt_out';
    public const COL_ROUTINE_TYPE = 'col_routine_type';
    public const COL_ROUTINE_TITLE = 'col_routine_title';
    public const COL_ROUTINE_USER_ID = 'col_routine_usr_id';

    // ilSrRoutineTable actions:
    public const ACTION_ROUTINE_RULES = 'action_routine_rules';
    public const ACTION_ROUTINE_ASSIGNMENTS = 'action_routine_assignments';
    public const ACTION_ROUTINE_EDIT = 'action_routine_edit';
    public const ACTION_ROUTINE_NOTIFICATIONS = 'action_routine_notifications';
    public const ACTION_ROUTINE_DELETE = 'action_routine_delete';

    // ilSrRoutineTable language variables:
    protected const STATUS_POSSIBLE = 'status_possible';
    protected const STATUS_IMPOSSIBLE = 'status_impossible';

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.routine_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TITLE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_USER_ID));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TYPE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_ELONGATION));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_HAS_OPT_OUT));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        // translate the status of 'opt_out_possible'.
        $status_opt_out = ($data[IRoutine::F_HAS_OPT_OUT]) ?
            $this->translator->txt(self::STATUS_POSSIBLE) :
            $this->translator->txt(self::STATUS_IMPOSSIBLE)
        ;

        // if the 'owner_id' still exists, get the login-name.
        $owner_name = (ilObjUser::_exists($data[IRoutine::F_USER_ID])) ?
            (new ilObjUser((int) $data[IRoutine::F_USER_ID]))->getLogin() : ''
        ;

        // translate the routine type.
        $routine_type = $this->translator->txt($data[IRoutine::F_ROUTINE_TYPE]);

        $template->setVariable(self::COL_ROUTINE_TITLE, $data[IRoutine::F_TITLE]);
        $template->setVariable(self::COL_ROUTINE_USER_ID, $owner_name);
        $template->setVariable(self::COL_ROUTINE_TYPE, $routine_type);
        $template->setVariable(self::COL_ROUTINE_ELONGATION, $data[IRoutine::F_ELONGATION]);
        $template->setVariable(self::COL_ROUTINE_HAS_OPT_OUT, $status_opt_out);
        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->getActionDropdown((int) $data[IRoutine::F_ROUTINE_ID])
            )
        );
    }

    /**
     * @param int $routine_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $routine_id) : Dropdown
    {
        $this->setActionParameters($routine_id);

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ROUTINE_RULES),
            $this->ctrl->getLinkTargetByClass(
                ilSrRuleGUI::class,
                ilSrRuleGUI::CMD_INDEX
            )
        );

        // this action is only necessary if the user can manage assignments.
        if ($this->access_handler->canManageAssignments()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_ASSIGNMENTS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_INDEX
                )
            );
        }

        // these actions are only necessary if the user can manage routines.
        if ($this->access_handler->canManageRoutines()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_NOTIFICATIONS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_INDEX
                )
            );

            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_EDIT),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_EDIT
                )
            );

            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_DELETE
                )
            );
        }

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $routine_id
     */
    protected function setActionParameters(int $routine_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::PARAM_ROUTINE_ID,
            $routine_id
        );
    }
}