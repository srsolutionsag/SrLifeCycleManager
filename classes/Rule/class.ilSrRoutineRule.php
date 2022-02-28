<?php // strict types are not possible with ActiveRecord.

use srag\Plugins\_SrLifeCycleManager\Rule\IRoutineRuleRelation;

/**
 * Routine-Rule relationship DAO
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineRule extends ActiveRecord implements IRoutineRuleRelation
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine_rule';

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
    protected $relation_id;

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
    public function getRelationId() : ?int
    {
        return $this->relation_id;
    }

    /**
     * @inheritDoc
     */
    public function setRelationId(?int $relation_id) : IRoutineRuleRelation
    {
        $this->relation_id = $relation_id;
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
    public function setRoutineId(?int $routine_id) : IRoutineRuleRelation
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
    public function setRuleId(?int $rule_id) : IRoutineRuleRelation
    {
        $this->rule_id = $rule_id;
        return $this;
    }
}