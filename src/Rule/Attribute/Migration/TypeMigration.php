<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration;

/**
 * Migration to change types of dynamic attributes used in
 * existing rules.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @see    ../../../sql/dbupdate.php
 */
class TypeMigration extends AttributeMigration
{
    /**
     * @var string
     */
    protected $lhs_value_column;

    /**
     * @var string
     */
    protected $lhs_type_column;

    /**
     * @var string
     */
    protected $rhs_value_column;

    /**
     * @var string
     */
    protected $rhs_type_column;

    /**
     * @inheritDoc
     */
    public function __construct(
        \ilDBInterface $database,
        string $table_name,
        string $lhs_value_column,
        string $lhs_type_column,
        string $rhs_value_column,
        string $rhs_type_column
    ) {
        parent::__construct($database, $table_name);

        $this->checkTableColumn($lhs_value_column);
        $this->checkTableColumn($lhs_type_column);
        $this->checkTableColumn($rhs_value_column);
        $this->checkTableColumn($rhs_type_column);

        $this->lhs_value_column = $lhs_value_column;
        $this->lhs_type_column = $lhs_type_column;
        $this->rhs_value_column = $rhs_value_column;
        $this->rhs_type_column = $rhs_type_column;
    }

    /**
     * Changes all LHS and RHS types for the given value from
     * one string to another.
     */
    public function migrateForValue(string $from_type, string $to_type, string $of_value): void
    {
        $this->database->update($this->table_name, [
            $this->lhs_type_column => ['text', $to_type],
        ], [
            $this->lhs_type_column => ['text', $from_type],
            $this->lhs_value_column => ['text', $of_value],
        ]);

        $this->database->update($this->table_name, [
            $this->rhs_type_column => ['text', $to_type],
        ], [
            $this->rhs_type_column => ['text', $from_type],
            $this->rhs_value_column => ['text', $of_value],
        ]);
    }

    /**
     * Changes all LHS and RHS types from one string to another
     * regardless of the value.
     */
    public function migrateAll(string $from_type, string $to_type): void
    {
        $this->database->update($this->table_name, [
            $this->lhs_type_column => ['text', $to_type],
        ], [
            $this->lhs_type_column => ['text', $from_type],
        ]);

        $this->database->update($this->table_name, [
            $this->rhs_type_column => ['text', $to_type],
        ], [
            $this->rhs_type_column => ['text', $from_type],
        ]);
    }
}
