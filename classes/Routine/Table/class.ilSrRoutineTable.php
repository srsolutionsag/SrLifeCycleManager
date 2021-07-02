<?php

use ILIAS\UI\Component\Dropdown\Standard as Dropdown;

/**
 * Class ilSrRoutineTable represents all available routines.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineTable extends ilSrAbstractMainTable
{
    /**
     * ilSrRoutineTable column names.
     */
    private const COL_REF_ID            = 'col_routine_ref_id';
    private const COL_ACTIVE            = 'col_routine_active';
    private const COL_ORIGIN_TYPE       = 'col_routine_origin_type';
    private const COL_OWNER             = 'col_routine_owner_id';
    private const COL_CREATION_DATE     = 'col_routine_creation_date';
    private const COL_OPT_OUT_POSSIBLE  = 'col_routine_opt_out_possible';
    private const COL_ELONGATION_DAYS   = 'col_routine_elongation_days';
    private const COL_ACTIONS           = 'col_actions';

    /**
     * ilSrRoutineTable lang vars
     */
    private const STATUS_ACTIVE     = 'status_active';
    private const STATUS_INACTIVE   = 'status_inactive';
    private const STATUS_POSSIBLE   = 'status_possible';
    private const STATUS_IMPOSSIBLE = 'status_impossible';

    /**
     * @inheritDoc
     */
    protected function getRowTemplate() : string
    {
        return 'tpl.routine_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function getTableData() : array
    {
        return $this->repository->routine()->getAllAsArray();
    }

    /**
     * @inheritDoc
     */
    protected function getTableColumns() : array
    {
        return [
            self::COL_REF_ID,
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
        $status_active = ($row_data[ilSrRoutine::F_ACTIVE]) ?
            $this->plugin->txt(self::STATUS_ACTIVE) :
            $this->plugin->txt(self::STATUS_INACTIVE)
        ;

        $status_opt_out = ($row_data[ilSrRoutine::F_OPT_OUT_POSSIBLE]) ?
            $this->plugin->txt(self::STATUS_POSSIBLE) :
            $this->plugin->txt(self::STATUS_IMPOSSIBLE)
        ;

        $owner_name = (ilObjUser::_exists($row_data[ilSrRoutine::F_OWNER_ID])) ?
            (new ilObjUser($row_data[ilSrRoutine::F_OWNER_ID]))->getLogin() : ''
        ;

        $creation_date = $row_data[ilSrRoutine::F_CREATION_DATE]->format(self::VISUAL_DATETIME_FORMAT);
        $origin_type   = $this->plugin->txt(ilSrRoutine::ORIGIN_TYPE_NAMES[$row_data[ilSrRoutine::F_ORIGIN_TYPE]]);

        $template->setVariable(strtoupper(self::COL_REF_ID), $row_data[ilSrRoutine::F_REF_ID]);
        $template->setVariable(strtoupper(self::COL_ACTIVE), $status_active);
        $template->setVariable(strtoupper(self::COL_ORIGIN_TYPE), $origin_type);
        $template->setVariable(strtoupper(self::COL_OWNER), $owner_name);
        $template->setVariable(strtoupper(self::COL_CREATION_DATE), $creation_date);
        $template->setVariable(strtoupper(self::COL_OPT_OUT_POSSIBLE), $status_opt_out);
        $template->setVariable(strtoupper(self::COL_ELONGATION_DAYS), $row_data[ilSrRoutine::F_ELONGATION_DAYS]);
        $template->setVariable(strtoupper(self::COL_ACTIONS), $this->ui->renderer()->render(
            $this->getActionDropdown($row_data[ilSrRoutine::F_ID]))
        );
    }

    /**
     * returns an action dropdown for each routine row-entry.
     *
     * @param int $routine_id
     * @return Dropdown
     */
    private function getActionDropdown(int $routine_id) : Dropdown
    {
        // in order to generate individual links for each row-entry
        // action, the id of the routine has to be added as a GET
        // query parameter.
        $this->ctrl->setParameter(
            $this->parent_obj,
            ilSrRoutineGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );

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
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_RULE_MANAGE
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
}