<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IAttribute
{
    /**
     * @var string datetime format for date comparisons.
     */
    public const COMPARABLE_DATETIME_FORMAT = 'Y-m-d';

    /**
     * Comparable value types.
     */
    public const COMPARABLE_VALUE_TYPE_ARRAY    = 'array';
    public const COMPARABLE_VALUE_TYPE_STRING   = 'string';
    public const COMPARABLE_VALUE_TYPE_BOOL     = 'boolean';
    public const COMPARABLE_VALUE_TYPE_INT      = 'integer';
    public const COMPARABLE_VALUE_TYPE_NULL     = 'NULL';
    public const COMPARABLE_VALUE_TYPE_DATE     = 'DateTime';

    /**
     * @return string[]
     */
    public function getComparableValueTypes() : array;

    /**
     * @param string $type
     * @return mixed|null
     */
    public function getComparableValue(string $type);
}