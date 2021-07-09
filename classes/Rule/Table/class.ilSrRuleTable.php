<?php

use ILIAS\UI\Component\Dropdown\Standard as Dropdown;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRuleTable
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilSrRuleTable extends ilSrAbstractMainTable
{
    /**
     * ilSrRuleTable column names.
     */
    private const COL_RULE_RHS_TYPE  = 'col_rule_rhs_type';
    private const COL_RULE_RHS_VALUE = 'col_rule_rhs_value';
    private const COL_RULE_OPERATOR  = 'col_rule_operator';
    private const COL_RULE_LHS_TYPE  = 'col_rule_rhs_type';
    private const COL_RULE_LHS_VALUE = 'col_rule_rhs_value';
    private const COL_ACTIONS        = 'col_actions';

    /**
     * @var IRoutine
     */
    private $routine;

    /**
     * ilSrRuleTable constructor.
     *
     * @param object   $parent_gui
     * @param string   $parent_cmd
     * @param IRoutine $routine
     */
    public function __construct(object $parent_gui, string $parent_cmd, IRoutine $routine)
    {
        // dependencies must be declared before the parent constructor
        // is called, as they're already used by it.
        $this->routine = $routine;

        parent::__construct($parent_gui, $parent_cmd);
    }

    /**
     * @inheritDoc
     */
    protected function getTableData() : array
    {
        return $this->repository->routine()->getRules($this->routine, true);
    }

    /**
     * @inheritDoc
     */
    protected function getRowTemplate() : string
    {
        return 'tpl.rule_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function getTableColumns() : array
    {
        return [
            self::COL_RULE_RHS_TYPE,
            self::COL_RULE_RHS_VALUE,
            self::COL_RULE_OPERATOR,
            self::COL_RULE_LHS_TYPE,
            self::COL_RULE_LHS_VALUE,
            ''
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void
    {
        $template->setVariable(strtoupper(self::COL_RULE_RHS_TYPE), $row_data[ilSrRule::F_RHS_TYPE]);
        $template->setVariable(strtoupper(self::COL_RULE_RHS_VALUE), $row_data[ilSrRule::F_RHS_VALUE]);
        $template->setVariable(strtoupper(self::COL_RULE_OPERATOR), $row_data[ilSrRule::F_OPERATOR]);
        $template->setVariable(strtoupper(self::COL_RULE_LHS_TYPE), $row_data[ilSrRule::F_LHS_TYPE]);
        $template->setVariable(strtoupper(self::COL_RULE_LHS_VALUE), $row_data[ilSrRule::F_LHS_VALUE]);
        $template->setVariable(strtoupper(self::COL_ACTIONS), $this->ui->renderer()->render(
            $this->getActionDropdown($row_data[ilSrRule::F_ID])
        ));
    }

    /**
     * returns an action dropdown for each rule row-entry.
     *
     * @param int $rule_id
     * @return Dropdown
     */
    private function getActionDropdown(int $rule_id) : Dropdown
    {
        $this->ctrl->setParameterByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::QUERY_PARAM_RULE_ID,
            $rule_id
        );

        return $this->ui->factory()->dropdown()->standard([
            $this->ui->factory()->button()->shy(
                $this->plugin->txt(ilSrRuleGUI::ACTION_RULE_DELETE),
                $this->ctrl->getLinkTargetByClass(
                    ilSrRuleGUI::class,
                    ilSrRuleGUI::CMD_RULE_DELETE
                )
            ),
        ]);
    }
}