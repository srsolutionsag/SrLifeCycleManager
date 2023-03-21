<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRuleTable extends ilSrAbstractTable
{
    // ilSrRuleTable table columns:
    public const COL_RULE_RHS_TYPE = 'col_rule_rhs_type';
    public const COL_RULE_RHS_VALUE = 'col_rule_rhs_value';
    public const COL_RULE_OPERATOR = 'col_rule_operator';
    public const COL_RULE_LHS_TYPE = 'col_rule_lhs_type';
    public const COL_RULE_LHS_VALUE = 'col_rule_lhs_value';

    // ilSrRuleTable table actions:
    public const ACTION_RULE_EDIT = 'action_rule_edit';
    public const ACTION_RULE_DELETE = 'action_rule_delete';

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    public function __construct(
        Factory $ui_factory,
        Renderer $renderer,
        ITranslator $translator,
        AttributeFactory $attribute_factory,
        ilSrAccessHandler $access_handler,
        ilCtrl $ctrl,
        object $parent_gui_object,
        string $parent_gui_cmd,
        array $table_data
    ) {
        parent::__construct(
            $ui_factory,
            $renderer,
            $translator,
            $access_handler,
            $ctrl,
            $parent_gui_object,
            $parent_gui_cmd,
            $table_data
        );

        $this->attribute_factory = $attribute_factory;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateName(): string
    {
        return 'tpl.rule_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns(): void
    {
        $this->addColumn($this->translator->txt(self::COL_RULE_LHS_TYPE));
        $this->addColumn($this->translator->txt(self::COL_RULE_LHS_VALUE));
        $this->addColumn($this->translator->txt(self::COL_RULE_OPERATOR));
        $this->addColumn($this->translator->txt(self::COL_RULE_RHS_TYPE));
        $this->addColumn($this->translator->txt(self::COL_RULE_RHS_VALUE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data): void
    {
        $template->setVariable(self::COL_RULE_LHS_TYPE, $this->translator->txt($data[IRule::F_LHS_TYPE]));
        $template->setVariable(
            self::COL_RULE_LHS_VALUE,
            $this->getMaybeTranslatedValue(
                $data[IRule::F_LHS_TYPE],
                $data[IRule::F_LHS_VALUE]
            )
        );

        $template->setVariable(self::COL_RULE_RHS_TYPE, $this->translator->txt($data[IRule::F_RHS_TYPE]));
        $template->setVariable(
            self::COL_RULE_RHS_VALUE,
            $this->getMaybeTranslatedValue(
                $data[IRule::F_RHS_TYPE],
                $data[IRule::F_RHS_VALUE]
            )
        );

        $template->setVariable(self::COL_RULE_OPERATOR, $this->translator->txt($data[IRule::F_OPERATOR]));
        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->getActionDropdown(
                    (int) $data[IRule::F_RULE_ID]
                )
            )
        );
    }

    /**
     * @param int $rule_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $rule_id): Dropdown
    {
        $this->ctrl->setParameterByClass(
            ilSrRuleGUI::class,
            ilSrRuleGUI::PARAM_RULE_ID,
            $rule_id
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_RULE_EDIT),
            $this->ctrl->getLinkTargetByClass(
                ilSrRuleGUI::class,
                ilSrRuleGUI::CMD_RULE_EDIT
            )
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_RULE_DELETE),
            $this->ctrl->getLinkTargetByClass(
                ilSrRuleGUI::class,
                ilSrRuleGUI::CMD_RULE_DELETE
            )
        );

        return $this->ui_factory->dropdown()->standard($actions);
    }

    /**
     * @param string $attr_type
     * @param mixed  $attr_value
     * @return string
     */
    protected function getMaybeTranslatedValue(string $attr_type, $attr_value): string
    {
        // common attributes must not be translated because they
        // hold user generated values.
        if (in_array($attr_type, $this->attribute_factory->getAttributeValues(CommonAttribute::class), true)) {
            return $attr_value;
        }

        return $this->translator->txt($attr_value);
    }
}
