<?php declare(strict_types=1);

use ILIAS\UI\Component\Dropdown\Standard as Dropdown;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\DI\UIServices;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

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
    protected const COL_RULE_LHS_TYPE  = 'col_rule_lhs_type';
    protected const COL_RULE_LHS_VALUE = 'col_rule_lhs_value';
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
        $template->setVariable(strtoupper(self::COL_RULE_RHS_TYPE), $this->plugin->txt($row_data[IRule::F_RHS_TYPE]));
        $template->setVariable(
            strtoupper(self::COL_RULE_RHS_VALUE),
            $this->getMaybeTranslatedValue(
                $row_data[IRule::F_RHS_TYPE],
                $row_data[IRule::F_RHS_VALUE]
            )
        );

        $template->setVariable(strtoupper(self::COL_RULE_LHS_TYPE), $this->plugin->txt($row_data[IRule::F_LHS_TYPE]));
        $template->setVariable(
            strtoupper(self::COL_RULE_LHS_VALUE),
            $this->getMaybeTranslatedValue(
                $row_data[IRule::F_LHS_TYPE],
                $row_data[IRule::F_LHS_VALUE]
            )
        );

        $template->setVariable(strtoupper(self::COL_RULE_OPERATOR), $this->plugin->txt($row_data[IRule::F_OPERATOR]));
        $template->setVariable(strtoupper(self::COL_ACTIONS), $this->ui->renderer()->render(
            $this->getActionDropdown($row_data[IRule::F_RULE_ID])
        ));
    }

    /**
     * @param string $attr_type
     * @param mixed  $attr_value
     * @return string
     */
    protected function getMaybeTranslatedValue(string $attr_type, $attr_value) : string
    {
        // common attributes must not be translated because they
        // hold user generated values.
        if (in_array($attr_type, CommonAttribute::COMMON_ATTRIBUTES, true)) {
            return $attr_value;
        }

        return $this->plugin->txt($attr_value);
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