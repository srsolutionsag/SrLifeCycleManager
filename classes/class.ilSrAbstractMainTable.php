<?php

/**
 * Class ilSrAbstractMainTable
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilSrAbstractMainTable extends ilTable2GUI
{
    /**
     * @var string datetime-format for representation.
     */
    protected const VISUAL_DATETIME_FORMAT = 'm-d-Y';

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * ilSrAbstractMainTable constructor
     *
     * @param Object $parent_gui
     * @param string $parent_cmd
     */
    public function __construct(Object $parent_gui, string $parent_cmd)
    {
        global $DIC;

        $this->ui         = $DIC->ui();
        $this->plugin     = ilSrLifeCycleManagerPlugin::getInstance();
        $this->repository = ilSrLifeCycleManagerRepository::getInstance();

        parent::__construct($parent_gui, $parent_cmd);

        $this->setId(static::class);
        $this->setPrefix(ilSrLifeCycleManagerPlugin::PLUGIN_ID);
        $this->setTableColumns($this->getTableColumns());
        $this->setRowTemplate(
            $this->getRowTemplate(),
            './Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/'
        );
        $this->setData($this->getTableData());
    }

    /**
     * This method MUST return the table-data as an array.
     *
     * The array result cannot be null, but empty. It's also NOT
     * possible to have an array of objects, this leads to an error
     * due to ilTable2GUI. The array MUST therefore consist of further
     * arrays.
     *
     * @return array
     */
    abstract protected function getTableData() : array;

    /**
     * This method MUST return the name of a row-template.
     *
     * Templates of derived classes (tables) must be located in
     * /templates/default/ in order to be loaded properly.
     *
     * Templates SHOULD contain the HTML markup of one '<tr>' element, which
     * holds a '<td>' element for each column added. In order to fill in data
     * ILIAS template-variables can be used, which looks something like:
     *
     *      <tr>
     *          <td>{COLUMN_ONE_VALUE}</td>
     *          ...
     *      </tr>
     *
     * @return string
     */
    abstract protected function getRowTemplate() : string;

    /**
     * This method MUST return all available column ids.
     *
     * The column-id is used as a lang-var, make sure to provide an
     * according entry in /lang directory.
     *
     * An empty string can be provided as well, which indicates an
     * actions column, which has limited width and no title.
     *
     * @return string[]
     */
    abstract protected function getTableColumns() : array;

    /**
     * This method MUST prepare the given template, set the variables.
     *
     * The template is passed to derived classes by reference, therefore
     * it must not be returned. The second argument contains the data of
     * the current row entry, provided by
     *
     * @see ilSrAbstractMainTable::getTableData()
     */
    abstract protected function prepareRowTemplate(ilTemplate $template, array $row_data) : void;

    /**
     * Renders a table-row entry.
     *
     * Overwrites ilTable2GUI's method and serves as an adapter
     * method to declare an abstract method for derived classes:
     * @see ilSrAbstractMainTable::prepareRowTemplate()
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
     * Adds the columns of the derived class to the table.
     *
     * @see ilSrAbstractMainTable::getTableColumns()
     *
     * @param array $columns
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