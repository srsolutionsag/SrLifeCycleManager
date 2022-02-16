<?php

use ILIAS\DI\UIServices;

/**
 * Class ilSrAbstractMainTable
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ilSrAbstractTable extends ilTable2GUI
{
    /**
     * @var string datetime-format for representation.
     */
    protected const VISUAL_DATETIME_FORMAT = 'm-d-Y';

    /**
     * @var UIServices
     */
    protected $ui;

    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @param UIServices                 $ui
     * @param ilSrLifeCycleManagerPlugin $plugin
     * @param Object                     $parent_gui
     * @param string                     $parent_cmd
     * @param string                     $row_template
     * @param array                      $table_data
     */
    public function __construct(
        UIServices $ui,
        ilSrLifeCycleManagerPlugin $plugin,
        Object $parent_gui,
        string $parent_cmd,
        string $row_template,
        array $table_data
    ) {
        parent::__construct($parent_gui, $parent_cmd);

        $this->ui     = $ui;
        $this->plugin = $plugin;

        $this->setId(static::class);
        $this->setPrefix($plugin->getPluginId());
        $this->setTableColumns($this->getTableColumns());
        $this->setRowTemplate(
            $row_template,
            // this method accepts a template-dir argument which
            // indicates where templates are located. But somehow
            // if not the plugin-root directory is passed the
            // templates in '/templates/default/' are not found.
            $plugin->getPluginDir()
        );

        $this->setData($table_data);
    }

    /**
     * Renders a table-row entry.
     *
     * Overwrites ilTable2GUI's method and serves as an adapter
     * method to declare an abstract method for derived classes:
     *
     * @see ilSrAbstractTable::prepareRowTemplate()
     *
     * @param array $a_set
     */
    protected function fillRow($a_set) : void
    {
        // the template is passed by reference, therefore it must
        // not be allocated again.
        $this->prepareRowTemplate($this->tpl, $a_set);
    }

    /**
     * This method MUST prepare the given template, set the variables.
     *
     * The template is passed to derived classes by reference, therefore
     * it must not be returned. The second argument contains the data of
     * the current row entry.
     */
    abstract protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void;

    /**
     * This method MUST return all available column ids.
     *
     * The column-id is used as a lang-var, make sure to provide an
     * according entry in /lang directory.
     *
     * An empty string can be provided as well, which indicates an
     * action column, which has limited width and no title.
     *
     * @return string[]
     */
    abstract protected function getTableColumns() : array;

    /**
     * Adds the columns of the derived class to the table.
     * @param array $columns
     *@see ilSrAbstractTable::getTableColumns()
     */
    private function setTableColumns(array $columns) : void
    {
        if (empty($columns)) {
            // abort if getTableColumns() returned an empty array.
            throw new LogicException(static::class . "::getTableColumns() provided empty array, at least one column is expected.");
        }

        foreach ($columns as $column /*=> $options*/) {
            if (empty($column)) {
                // if an empty column id is provided it's recognized
                // as an empty actions column, where only dropdowns
                // will be added. Hence the empty strings and limited
                // column-width.
                $this->addColumn('', '', '50px');
            } else {
                $this->addColumn($this->plugin->txt($column));
            }
        }
    }
}