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
 * Migration to change values of dynamic attributes used in
 * existing rules.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @see    ../../../sql/dbupdate.php
 */
class ValueMigration extends AttributeMigration
{
    protected string $lhs_value_column;

    protected string $rhs_value_column;

    /**
     * @inheritDoc
     */
    public function __construct(
        \ilDBInterface $database,
        string $table_name,
        string $lhs_value_column,
        string $rhs_value_column
    ) {
        parent::__construct($database, $table_name);

        $this->checkTableColumn($lhs_value_column);
        $this->checkTableColumn($rhs_value_column);

        $this->lhs_value_column = $lhs_value_column;
        $this->rhs_value_column = $rhs_value_column;
    }

    /**
     * Changes all LHS and RHS values from one string to another.
     */
    public function migrateAll(string $from_type, string $to_type): void
    {
        $this->database->update($this->table_name, [
            $this->lhs_value_column => ['text', $to_type],
        ], [
            $this->lhs_value_column => ['text', $from_type],
        ]);

        $this->database->update($this->table_name, [
            $this->rhs_value_column => ['text', $to_type],
        ], [
            $this->rhs_value_column => ['text', $from_type],
        ]);
    }
}
