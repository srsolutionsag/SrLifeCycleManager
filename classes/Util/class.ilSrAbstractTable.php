<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;

/**
 * This is an abstraction for ILIAS ilTable2GUI implementations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The table wraps the table-gui implementation, so that their generation can
 * be unified and derived classes must only bother about rendering table rows
 * and defining the table columns.
 *
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractTable extends ilTable2GUI
{
    /**
     * @var string column name and id for action dropdowns.
     */
    public const COL_ACTIONS = 'col_actions';

    /**
     * @param Factory           $ui_factory
     * @param Renderer          $renderer
     * @param ITranslator       $translator
     * @param ilSrAccessHandler $access_handler
     * @param ilCtrl            $ctrl
     * @param object            $parent_gui
     * @param string            $parent_gui_cmd
     * @param array             $table_data
     */
    public function __construct(
        protected Factory $ui_factory,
        protected Renderer $renderer,
        protected ITranslator $translator,
        protected IGeneralRepository $general_repository,
        protected \ilSrAccessHandler $access_handler,
        ilCtrl $ctrl,
        protected object $parent_gui,
        string $parent_gui_cmd,
        array $table_data
    ) {
        $this->ctrl = $ctrl;

        $this->setId(static::class);
        $this->setPrefix(ilSrLifeCycleManagerPlugin::PLUGIN_ID);
        $this->setRowTemplate(
            $this->getTemplateName(),
            ilSrLifeCycleManagerPlugin::PLUGIN_DIR
        );

        parent::__construct($this->parent_gui, $parent_gui_cmd);

        $this->addTableColumns();
        $this->setData($table_data);
    }

    /**
     * Returns the table wrapped in a UI component.
     *
     * @return Component
     */
    public function getTable(): Component
    {
        return $this->ui_factory->legacy($this->getHTML());
    }

    /**
     * This method MUST return the name of the tables row-template.
     *
     * The provided template must be located within '/templates/default/',
     * whereas the first slash is the plugin root.
     *
     * @return string
     */
    abstract protected function getTemplateName(): string;

    /**
     * This method MUST add this tables columns.
     *
     * In order to add columns, the parent method @see ilTable2GUI::addColumn()
     * must be used.
     */
    abstract protected function addTableColumns(): void;

    /**
     * This method MUST prepare the given template which serves as ONE row-entry.
     *
     * The array-data as second argument contains ONE entry of the data provided
     * in the table constructor: @param ilTemplate $template
     * @param array $data
     * @see ilSrAbstractTable::__construct().
     *
     */
    abstract protected function renderTableRow(ilTemplate $template, array $data): void;

    /**
     * Adds an empty column to the current table with a fixed width, just
     * enough for action dropdowns.
     *
     * @see \ILIAS\UI\Component\Dropdown\Dropdown
     */
    protected function addActionColumn(): void
    {
        // empty column with fixed width for action dropdowns.
        $this->addColumn($this->translator->txt(self::COL_ACTIONS), '', '50px');
    }

    /**
     * Renders a table-row.
     *
     * Overwrites ilTable2GUI's method and serves as an adapter
     * method to declare an abstract method for derived classes:
     *
     * @param array<string, mixed> $a_set
     * @see ilSrAbstractTable::renderTableRow()
     *
     */
    protected function fillRow($a_set): void
    {
        $this->renderTableRow($this->tpl, $a_set);
    }

    protected function getUserName(int $user_id): string
    {
        $user = $this->general_repository->getUser($user_id);
        if (null !== $user) {
            return $user->getLogin();
        }

        return '';
    }
}
