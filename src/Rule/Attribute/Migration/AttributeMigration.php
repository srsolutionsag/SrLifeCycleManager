<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AttributeMigration
{
    protected string $table_name;

    /**
     * @throws \LogicException if the database table or columns do not exist.
     */
    public function __construct(protected \ilDBInterface $database, string $table_name)
    {
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
