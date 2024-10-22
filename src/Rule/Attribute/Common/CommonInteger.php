<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonInteger extends CommonAttribute
{
    /**
     * @var int
     */
    protected $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return $this->value;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return (string) $this->value;

            default:
                return null;
        }
    }
}