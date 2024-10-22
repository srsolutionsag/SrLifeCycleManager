<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AttributeMigration
{
    /**
     * @var \ilDBInterface
     */
    protected $database;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * @throws \LogicException if the database table or columns do not exist.
     */
    public function __construct(\ilDBInterface $database, string $table_name)
    {
        $this->database = $database;
        $this->checkTable($table_name);
        $this->table_name = $table_name;
    }

    protected function checkTableColumn(string $column): void
    {
        if (!$this->database->tableColumnExists($this->table_name, $column)) {
            throw new \LogicException("Database table column '$this->table_name.$column' does not exist (anymore).");
        }
    }

    protected function checkTable(string $table): void
    {
        if (!$this->database->tableExists($table)) {
            throw new \LogicException("Database table '$table' does not exist (anymore).");
        }
    }
}
