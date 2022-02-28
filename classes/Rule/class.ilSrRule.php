<?php // strict types are not possible with ActiveRecord.

use srag\Plugins\_SrLifeCycleManager\Rule\IRule;

/**
 * Rule DAO
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ilSrRule extends ActiveRecord implements IRule
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_rule';

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
    protected $rule_id;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      255
     */
    protected $lhs_type;

    /**
     * @var mixed
     *
     * @con_has_field   true
     * @con_is_notnull  false
     * @con_fieldtype   clob
     * @con_length      4000
     */
    protected $lhs_value;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      64
     */
    protected $operator;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      255
     */
    protected $rhs_type;

    /**
     * @var mixed
     *
     * @con_has_field   true
     * @con_is_notnull  false
     * @con_fieldtype   clob
     * @con_length      4000
     */
    protected $rhs_value;

    /**
     * @inheritDoc
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * @return int|null
     */
    public function getRuleId() : ?int
    {
        return $this->rule_id;
    }

    /**
     * @param int|null $rule_id
     * @return ilSrRule
     */
    public function setRuleId(?int $rule_id) : IRule
    {
        $this->rule_id = $rule_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLhsType() : string
    {
        return $this->lhs_type;
    }

    /**
     * @param string $type
     * @return ilSrRule
     */
    public function setLhsType(string $type) : IRule
    {
        $this->lhs_type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLhsValue()
    {
        return $this->lhs_value;
    }

    /**
     * @param mixed $value
     * @return ilSrRule
     */
    public function setLhsValue($value) : IRule
    {
        $this->lhs_value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @return ilSrRule
     */
    public function setOperator(string $operator) : IRule
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getRhsType() : string
    {
        return $this->rhs_type;
    }

    /**
     * @param string $type
     * @return ilSrRule
     */
    public function setRhsType(string $type) : IRule
    {
        $this->rhs_type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRhsValue()
    {
        return $this->rhs_value;
    }

    /**
     * @param mixed $value
     * @return ilSrRule
     */
    public function setRhsValue($value) : IRule
    {
        $this->rhs_value = $value;
        return $this;
    }
}