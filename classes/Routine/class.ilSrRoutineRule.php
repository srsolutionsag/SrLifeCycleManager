<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRule;

/**
 * Class ilSrRoutineRule is responsible for storing routine-rule (m:m) relations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineRule extends ActiveRecord implements IRoutineRule
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine_rule';

    /**
     * ilSrRoutineRule attribute names
     */
    public const F_ID           = 'id';
    public const F_ROUTINE_ID   = 'routine_id';
    public const F_RULE_ID      = 'rule_id';

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_is_primary  true
     * @con_is_notnull  true
     * @con_sequence    true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $id;

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $routine_id;

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $rule_id;

    /**
     * @inheritDoc
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(?int $id) : IRoutineRule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId() : ?int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(?int $routine_id) : IRoutineRule
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRuleId() : ?int
    {
        return $this->rule_id;
    }

    /**
     * @inheritDoc
     */
    public function setRuleId(?int $rule_id) : IRoutineRule
    {
        $this->rule_id = $rule_id;
        return $this;
    }
}