<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Rule implements IRule
{
    /**
     * @param string $lhs_type
     * @param string $operator
     * @param string $rhs_type
     * @param int $routine_id
     * @param int|null $rule_id
     */
    public function __construct(
        protected string $lhs_type,
        protected mixed $lhs_value,
        protected string $operator,
        protected string $rhs_type,
        protected mixed $rhs_value,
        protected int $routine_id,
        protected ?int $rule_id = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRuleId(): ?int
    {
        return $this->rule_id;
    }

    /**
     * @inheritDoc
     */
    public function setRuleId(?int $rule_id): IRule
    {
        $this->rule_id = $rule_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId(): int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id): IRule
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLhsType(): string
    {
        return $this->lhs_type;
    }

    /**
     * @inheritDoc
     */
    public function setLhsType(string $type): IRule
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
    public function setLhsValue($value): IRule
    {
        $this->lhs_value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @inheritDoc
     */
    public function setOperator(string $operator): IRule
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRhsType(): string
    {
        return $this->rhs_type;
    }

    /**
     * @inheritDoc
     */
    public function setRhsType(string $type): IRule
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
    public function setRhsValue($value): IRule
    {
        $this->rhs_value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTypeBySide(string $rule_side): ?string
    {
        return match ($rule_side) {
            self::RULE_SIDE_LEFT => $this->getLhsType(),
            self::RULE_SIDE_RIGHT => $this->getRhsType(),
            default => null,
        };
    }

    /**
     * @inheritDoc
     */
    public function getValueBySide(string $rule_side)
    {
        return match ($rule_side) {
            self::RULE_SIDE_LEFT => $this->getLhsValue(),
            self::RULE_SIDE_RIGHT => $this->getRhsValue(),
            default => null,
        };
    }
}
