<?php declare(strict_types=1);

use ILIAS\UI\Component\Dropdown\Standard as Dropdown;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\DI\UIServices;

/**
 * Class ilSrRuleTable
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRuleTable extends ilSrAbstractTable
{
    protected const COL_RULE_RHS_TYPE  = 'col_rule_rhs_type';
    protected const COL_RULE_RHS_VALUE = 'col_rule_rhs_value';
    protected const COL_RULE_OPERATOR  = 'col_rule_operator';
    protected const COL_RULE_LHS_TYPE  = 'col_rule_rhs_type';
    protected const COL_RULE_LHS_VALUE = 'col_rule_rhs_value';
    protected const COL_ACTIONS        = 'col_actions';

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param UIServices                 $ui
     * @param ilSrLifeCycleManagerPlugin $plugin
     * @param object                     $parent_gui
     * @param string                     $parent_cmd
     * @param string                     $row_template
     * @param array                      $table_data
     * @param IRoutine                   $routine
     */
    public function __construct(
        UIServices $ui,
        ilSrLifeCycleManagerPlugin $plugin,
        object $parent_gui,
        string $parent_cmd,
        string $row_template,
        array $table_data,
        IRoutine $routine
    ) {
        parent::__construct($ui, $plugin, $parent_gui, $parent_cmd, $row_template, $table_data);

        $this->routine = $routine;
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
            '',
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
    protected function getActionDropdown(int $rule_id) : Dropdown
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