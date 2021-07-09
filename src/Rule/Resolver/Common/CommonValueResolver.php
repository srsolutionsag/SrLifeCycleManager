<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Resolver\IValueResolver;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\Common\ICommonValue;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;

/**
 * Class CommonValueResolver
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package srag\Plugins\SrLifeCycleManager\Rule\Resolver\Common
 */
final class CommonValueResolver implements IValueResolver
{
    /**
     * @var string[] resolver value types
     */
    public const VALUE_TYPES = [
        self::VALUE_TYPE_STRING,
        self::VALUE_TYPE_INTEGER,
        self::VALUE_TYPE_BOOL,
        self::VALUE_TYPE_ARRAY,
    ];

    /**
     * supported attributes
     */
    public const VALUE_TYPE_STRING   = 'string';
    public const VALUE_TYPE_INTEGER  = 'integer';
    public const VALUE_TYPE_BOOL     = 'boolean';
    public const VALUE_TYPE_ARRAY    = 'array';

    /**
     * returns the given value typecasted according to the attribute (type) given.
     *
     * @param string $type
     * @param string $value
     * @return array|bool|int|string|null
     */
    public function resolveCommonAttribute(string $type, string $value)
    {
        switch ($type) {
            case self::VALUE_TYPE_STRING:
                return (string) $value;
            case self::VALUE_TYPE_INTEGER:
                return (int) $value;
            case self::VALUE_TYPE_BOOL:
                return (bool) $value;
            case self::VALUE_TYPE_ARRAY:
                return (array) $value;

            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function resolveLhsValue(IComparison $comparison)
    {
        return $this->resolveCommonAttribute(
            $comparison->getRule()->getLhsType(),
            $comparison->getRule()->getLhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function resolveRhsValue(IComparison $comparison)
    {
        return $this->resolveCommonAttribute(
            $comparison->getRule()->getLhsType(),
            $comparison->getRule()->getLhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return [
            self::VALUE_TYPE_STRING,
            self::VALUE_TYPE_INTEGER,
            self::VALUE_TYPE_BOOL,
            self::VALUE_TYPE_ARRAY,
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateValue($value) : bool
    {
        return in_array(gettype($value), $this->getAttributes());
    }
}