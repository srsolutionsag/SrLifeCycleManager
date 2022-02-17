<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * Rule DTO
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Rule implements IRoutineAwareRule
{
    /**
     * @var int|null
     */
    protected $rule_id;

    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var string
     */
    protected $lhs_type;

    /**
     * @var mixed
     */
    protected $lhs_value;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $rhs_type;

    /**
     * @var mixed
     */
    protected $rhs_value;

    /**
     * @param string   $lhs_type
     * @param mixed    $lhs_value
     * @param string   $operator
     * @param string   $rhs_type
     * @param mixed    $rhs_value
     * @param int      $routine_id
     * @param int|null $rule_id
     */
    public function __construct(
        string $lhs_type,
        $lhs_value,
        string $operator,
        string $rhs_type,
        $rhs_value,
        int $routine_id,
        int $rule_id = null
    ) {
        $this->rule_id = $rule_id;
        $this->routine_id = $routine_id;
        $this->lhs_type = $lhs_type;
        $this->lhs_value = $lhs_value;
        $this->operator = $operator;
        $this->rhs_type = $rhs_type;
        $this->rhs_value = $rhs_value;
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
     * @return IRule
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
     * @return IRule
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
     * @return IRule
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
     * @param string $type
     * @return IRule
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
     * @return IRule
     */
    public function setRhsValue($value) : IRule
    {
        $this->rhs_value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId() : int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : IRoutineAwareRule
    {
        $this->routine_id = $routine_id;
        return $this;
    }
}