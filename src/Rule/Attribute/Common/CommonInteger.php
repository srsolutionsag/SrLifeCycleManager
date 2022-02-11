<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonInteger extends CommonAttribute
{
    /**
     * @var int
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
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