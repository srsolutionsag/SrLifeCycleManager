<?php

declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
 * This is an abstraction for ILIAS ilTable2GUI implementations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var object
     */
    protected $parent_gui;

    /**
     * @param Factory           $ui_factory
     * @param Renderer          $renderer
     * @param ITranslator       $translator
     * @param ilSrAccessHandler $access_handler
     * @param ilCtrl            $ctrl
     * @param object            $parent_gui_object
     * @param string            $parent_gui_cmd
     * @param array             $table_data
     */
    public function __construct(
        Factory $ui_factory,
        Renderer $renderer,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilCtrl $ctrl,
        object $parent_gui_object,
        string $parent_gui_cmd,
        array $table_data
    ) {
        $this->translator = $translator;
        $this->access_handler = $access_handler;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->ctrl = $ctrl;
        $this->parent_gui = $parent_gui_object;

        $this->setId(static::class);
        $this->setPrefix(ilSrLifeCycleManagerPlugin::PLUGIN_ID);
        $this->setRowTemplate(
            $this->getTemplateName(),
            ilSrLifeCycleManagerPlugin::PLUGIN_DIR
        );

        parent::__construct($parent_gui_object, $parent_gui_cmd);

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
     * in the table constructor: @see ilSrAbstractTable::__construct().
     *
     * @param ilTemplate $template
     * @param array $data
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
     * @see ilSrAbstractTable::renderTableRow()
     *
     * @param array<string, mixed> $a_set
     */
    protected function fillRow($a_set): void
    {
        $this->renderTableRow($this->tpl, $a_set);
    }
}
