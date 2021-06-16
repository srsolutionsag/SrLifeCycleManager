<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * Class Rule (DTO)
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class Rule implements IRule
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
        $this->id           = $id;
        $this->lhs_type     = $lhs_type;
        $this->lhs_value    = $lhs_value;
        $this->operator     = $operator;
        $this->rhs_type     = $rhs_type;
        $this->rhs_value    = $rhs_value;
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
    public function setId(?int $id) : IRule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLhsType() : string
    {
        return $this->lhs_type;
    }

    /**
     * @inheritDoc
     */
    public function setLhsType(string $type) : IRule
    {
        $this->lhs_type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLhsValue()
    {
        return $this->lhs_value;
    }

    /**
     * @inheritDoc
     */
    public function setLhsValue($lhs_value) : IRule
    {
        $this->lhs_value = $lhs_value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * @inheritDoc
     */
    public function setOperator(string $operator) : IRule
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRhsType() : string
    {
        return $this->rhs_type;
    }

    /**
     * @inheritDoc
     */
    public function setRhsType(string $type) : IRule
    {
        $this->rhs_type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRhsValue()
    {
        return $this->rhs_value;
    }

    /**
     * @inheritDoc
     */
    public function setRhsValue($rhs_rhs_value) : IRule
    {
        $this->rhs_value = $rhs_rhs_value;
        return $this;
    }
}