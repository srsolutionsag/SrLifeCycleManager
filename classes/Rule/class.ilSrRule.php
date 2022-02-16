<?php

use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * Class ilSrRule is responsible for storing rules in the database.
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
    protected $id;

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
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ilSrRule
     */
    public function setId(?int $id) : IRule
    {
        $this->id = $id;
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
     * @param mixed $lhs_value
     * @return ilSrRule
     */
    public function setLhsValue($lhs_value) : IRule
    {
        $this->lhs_value = $lhs_value;
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
     * @param mixed $rhs_value
     * @return ilSrRule
     */
    public function setRhsValue($rhs_value) : IRule
    {
        $this->rhs_value = $rhs_value;
        return $this;
    }
}