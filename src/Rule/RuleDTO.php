<?php

namespace srag\Plugins\SrCourseManager\Rule;

use srag\Plugins\SrCourseManager\Rule\IRule;

/**
 * Class RuleDTO
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class RuleDTO implements IRule
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $lhs_type;

    /**
     * @var mixed
     */
    private $lhs_value;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $rhs_type;

    /**
     * @var mixed
     */
    private $rhs_value;

    /**
     * RuleDTO constructor.
     *
     * @param int|null $id
     * @param string   $lhs_type
     * @param mixed    $lhs_value
     * @param string   $operator
     * @param string   $rhs_type
     * @param mixed    $rhs_value
     */
    public function __construct(
        int $id = null,
        string $lhs_type,
        $lhs_value,
        string $operator,
        string $rhs_type,
        $rhs_value
    ) {
        $this->id = $id;
        $this->lhs_type = $lhs_type;
        $this->lhs_value = $lhs_value;
        $this->operator = $operator;
        $this->rhs_type = $rhs_type;
        $this->rhs_value = $rhs_value;
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
     * @return IRule
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
     * @param string $lhs_type
     * @return IRule
     */
    public function setLhsType(string $lhs_type) : IRule
    {
        $this->lhs_type = $lhs_type;
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
     * @return IRule
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
     * @return IRule
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
     * @param string $rhs_type
     * @return IRule
     */
    public function setRhsType(string $rhs_type) : IRule
    {
        $this->rhs_type = $rhs_type;
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
     * @return IRule
     */
    public function setRhsValue($rhs_value) : IRule
    {
        $this->rhs_value = $rhs_value;
        return $this;
    }
}