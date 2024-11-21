<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineTable extends ilSrAbstractTable
{
    // ilSrRoutineTable table columns:
    public const COL_ROUTINE_ELONGATION = 'col_routine_elongation';
    public const COL_ROUTINE_ELONGATION_COOLDOWN = 'col_routine_elongation_cooldown';
    public const COL_ROUTINE_HAS_OPT_OUT = 'col_routine_has_opt_out';
    public const COL_ROUTINE_TYPE = 'col_routine_type';
    public const COL_ROUTINE_TITLE = 'col_routine_title';
    public const COL_ROUTINE_USER_ID = 'col_routine_usr_id';

    // ilSrRoutineTable actions:
    public const ACTION_ROUTINE_RULES = 'action_routine_rules';
    public const ACTION_ROUTINE_ASSIGNMENTS = 'action_routine_assignments';
    public const ACTION_ROUTINE_EDIT = 'action_routine_edit';
    public const ACTION_ROUTINE_REMINDERS = 'action_routine_reminders';
    public const ACTION_ROUTINE_CONFIRMATIONS = 'action_routine_confirmations';
    public const ACTION_ROUTINE_WHITELIST = 'action_routine_whitelist';
    public const ACTION_ROUTINE_DELETE = 'action_routine_delete';

    // ilSrRoutineTable language variables:
    public const STATUS_POSSIBLE = 'status_possible';
    public const STATUS_IMPOSSIBLE = 'status_impossible';

    /**
     * @inheritDoc
     */
    protected function getTemplateName(): string
    {
        return 'tpl.routine_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TITLE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_USER_ID));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_TYPE));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_ELONGATION));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_ELONGATION_COOLDOWN));
        $this->addColumn($this->translator->txt(self::COL_ROUTINE_HAS_OPT_OUT));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data): void
    {
        // translate the status of 'opt_out_possible'.
        $status_opt_out = ($data[IRoutine::F_HAS_OPT_OUT]) ?
            $this->translator->txt(self::STATUS_POSSIBLE) :
            $this->translator->txt(self::STATUS_IMPOSSIBLE);

        // translate the routine type.
        $routine_type = $this->translator->txt($data[IRoutine::F_ROUTINE_TYPE]);

        $template->setVariable(self::COL_ROUTINE_TITLE, $data[IRoutine::F_TITLE]);
        $template->setVariable(self::COL_ROUTINE_USER_ID, $this->getUserName((int) $data[IRoutine::F_USER_ID]));
        $template->setVariable(self::COL_ROUTINE_TYPE, $routine_type);
        $template->setVariable(self::COL_ROUTINE_ELONGATION, $data[IRoutine::F_ELONGATION]);
        $template->setVariable(self::COL_ROUTINE_ELONGATION_COOLDOWN, $data[IRoutine::F_COOLDOWN]);
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
    protected function getActionDropdown(int $routine_id): Dropdown
    {
        $this->setActionParameters($routine_id);

        $actions = [];

        // this action is only necessary if the user can manage assignments.
        if ($this->access_handler->canManageAssignments()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_ASSIGNMENTS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrObjectAssignmentGUI::class,
                    ilSrObjectAssignmentGUI::CMD_INDEX
                )
            );
        }

        // these actions are only necessary if the user can manage routines.
        if ($this->access_handler->canManageRoutines()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_RULES),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRuleGUI::class,
                    ilSrRuleGUI::CMD_INDEX
                )
            );

            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_REMINDERS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrReminderGUI::class,
                    ilSrReminderGUI::CMD_INDEX
                )
            );

            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_CONFIRMATIONS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrConfirmationGUI::class,
                    ilSrConfirmationGUI::CMD_INDEX
                )
            );

            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_WHITELIST),
                $this->ctrl->getLinkTargetByClass(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_INDEX
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
    protected function setActionParameters(int $routine_id): void
    {
        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrObjectAssignmentGUI::class,
            ilSrObjectAssignmentGUI::PARAM_ROUTINE_ID,
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
            ilSrConfirmationGUI::class,
            ilSrConfirmationGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrReminderGUI::class,
            ilSrReminderGUI::PARAM_ROUTINE_ID,
            $routine_id
        );
    }
}
