<?php declare(strict_types=1);

use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilSrRoutineAssignmentTableHelper
{
    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return Dropdown
     */
    protected function getAssignmentActionDropdown(int $routine_id, int $ref_id) : Dropdown
    {
        $this->setAssignmentActionParameters($routine_id, $ref_id);

        $actions[self::ACTION_ASSIGNMENT_EDIT] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_EDIT),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineAssignmentGUI::class,
                ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_EDIT
            )
        );

        $actions[self::ACTION_ASSIGNMENT_DELETE] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ASSIGNMENT_DELETE),
            $this->ctrl->getLinkTargetByClass(
                ilSrRoutineAssignmentGUI::class,
                ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return void
     */
    protected function setAssignmentActionParameters(int $routine_id, int $ref_id) : void
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
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ASSIGNED_REF_ID,
            $ref_id
        );
    }
}