<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IAttribute
{
    public const COMPARABLE_VALUE_TYPE_ARRAY = 'array';
    public const COMPARABLE_VALUE_TYPE_STRING = 'string';
    public const COMPARABLE_VALUE_TYPE_BOOL = 'boolean';
    public const COMPARABLE_VALUE_TYPE_INT = 'integer';
    public const COMPARABLE_VALUE_TYPE_NULL = 'NULL';

    /**
     * @return string[]
     */
    public function getComparableTypes() : array;

    /**
     * @param string $type
     * @return mixed|null
     */
    public function getComparableValue(string $type);

    /**
     * @return string
     */
    public function getValue() : string;

    /**
     * @return string
     */
    public function getType() : string;
}