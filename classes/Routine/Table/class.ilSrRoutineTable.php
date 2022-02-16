<?php declare(strict_types=1);

use ILIAS\UI\Component\Dropdown\Standard as Dropdown;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRoutineTable represents all available routines.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineTable extends ilSrAbstractTable
{
    protected const STATUS_ACTIVE         = 'status_active';
    protected const STATUS_INACTIVE       = 'status_inactive';
    protected const STATUS_POSSIBLE       = 'status_possible';
    protected const STATUS_IMPOSSIBLE     = 'status_impossible';

    protected const COL_REF_ID            = 'col_routine_ref_id';
    protected const COL_NAME              = 'col_routine_name';
    protected const COL_ACTIVE            = 'col_routine_active';
    protected const COL_ORIGIN_TYPE       = 'col_routine_origin_type';
    protected const COL_OWNER             = 'col_routine_owner_id';
    protected const COL_CREATION_DATE     = 'col_routine_creation_date';
    protected const COL_OPT_OUT_POSSIBLE  = 'col_routine_opt_out_possible';
    protected const COL_ELONGATION_DAYS   = 'col_routine_elongation_days';
    protected const COL_ACTIONS           = 'col_actions';

    /**
     * @inheritDoc
     */
    protected function getTableColumns() : array
    {
        return [
            self::COL_REF_ID,
            self::COL_NAME,
            self::COL_ACTIVE,
            self::COL_ORIGIN_TYPE,
            self::COL_OWNER,
            self::COL_CREATION_DATE,
            self::COL_OPT_OUT_POSSIBLE,
            self::COL_ELONGATION_DAYS,
            '',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void
    {
        // translate the status of 'active'
        $status_active = ($row_data[IRoutine::F_ACTIVE]) ?
            $this->plugin->txt(self::STATUS_ACTIVE) :
            $this->plugin->txt(self::STATUS_INACTIVE)
        ;

        // translate the status of 'opt_out_possible'
        $status_opt_out = ($row_data[IRoutine::F_OPT_OUT_POSSIBLE]) ?
            $this->plugin->txt(self::STATUS_POSSIBLE) :
            $this->plugin->txt(self::STATUS_IMPOSSIBLE)
        ;

        // if the 'owner_id' still exists, get the login-name
        $owner_name = (ilObjUser::_exists($row_data[IRoutine::F_OWNER_ID])) ?
            (new ilObjUser($row_data[IRoutine::F_OWNER_ID]))->getLogin() : ''
        ;

        // format 'creation_date' to the visual format
        $creation_date = $row_data[IRoutine::F_CREATION_DATE]->format(self::VISUAL_DATETIME_FORMAT);

        // translate the 'origin_type' to it's mapped lang-var
        $origin_type   = $this->plugin->txt(IRoutine::ORIGIN_TYPE_NAMES[$row_data[IRoutine::F_ORIGIN_TYPE]]);

        $template->setVariable(strtoupper(self::COL_REF_ID), $row_data[IRoutine::F_REF_ID]);
        $template->setVariable(strtoupper(self::COL_NAME), $row_data[IRoutine::F_NAME]);
        $template->setVariable(strtoupper(self::COL_ACTIVE), $status_active);
        $template->setVariable(strtoupper(self::COL_ORIGIN_TYPE), $origin_type);
        $template->setVariable(strtoupper(self::COL_OWNER), $owner_name);
        $template->setVariable(strtoupper(self::COL_CREATION_DATE), $creation_date);
        $template->setVariable(strtoupper(self::COL_OPT_OUT_POSSIBLE), $status_opt_out);
        $template->setVariable(strtoupper(self::COL_ELONGATION_DAYS), $row_data[IRoutine::F_ELONGATION_DAYS]);
        $template->setVariable(strtoupper(self::COL_ACTIONS), $this->ui->renderer()->render(
            $this->getActionDropdown($row_data[IRoutine::F_ID])
        ));
    }

    /**
     * returns an action dropdown for each routine row-entry.
     *
     * @param int $routine_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $routine_id) : Dropdown
    {
        $this->setActionParameters($routine_id);
        return $this->ui->factory()->dropdown()->standard([
            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrRoutineGUI::ACTION_ROUTINE_EDIT),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_EDIT
                )
            ),

            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrRoutineGUI::ACTION_ROUTINE_RULES),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRuleGUI::class,
                    ilSrRuleGUI::CMD_INDEX
                )
            ),

            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrRoutineGUI::ACTION_ROUTINE_NOTIFICATIONS),
                $this->ctrl->getLinkTargetByClass(
                    ilSrNotificationGUI::class,
                    ilSrNotificationGUI::CMD_INDEX
                )
            ),

            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrRoutineGUI::ACTION_ROUTINE_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_DELETE
                )
            ),
        ]);
    }

    /**
     * Sets the given routine id for each class a link is built
     * to in @see ilSrRoutineTable::getActionDropdown().
     *
     * Note that this method MUST be called before links are
     * generated so that the links have the correct target.
     *
     * @param int $routine_id
     */
    protected function setActionParameters(int $routine_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrNotificationGUI::class,
            ilSrNotificationGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );
    }
}