<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IAttribute
{
    /**
     * Comparable value types.
     */
    public const COMPARABLE_VALUE_TYPE_ARRAY = 'array';
    public const COMPARABLE_VALUE_TYPE_STRING = 'string';
    public const COMPARABLE_VALUE_TYPE_BOOL = 'boolean';
    public const COMPARABLE_VALUE_TYPE_INT = 'integer';
    public const COMPARABLE_VALUE_TYPE_NULL = 'NULL';
    public const COMPARABLE_VALUE_TYPE_DATE = 'DateTime';

    /**
     * @var string [] available value types.
     */
    public const COMPARABLE_VALUE_TYPES = [
        self::COMPARABLE_VALUE_TYPE_ARRAY,
        self::COMPARABLE_VALUE_TYPE_STRING,
        self::COMPARABLE_VALUE_TYPE_BOOL,
        self::COMPARABLE_VALUE_TYPE_INT,
        self::COMPARABLE_VALUE_TYPE_DATE,
    ];

    /**
     * Returns a list of types the current attribute can be compared in.
     *
     * NOTE that the list should be ordered by priority in descending order,
     * so that the "most accurate" comparable type is the first array entry
     * and the "most inaccurate" the last.
     *
     * @return string[]
     */
    public function getComparableValueTypes(): array;

    /**
     * Returns the current attribute value cast to the given type.
     *
     * If the requested type is not supported by the attribute or couldn't
     * be cast, null is returned instead.
     *
     * @param string $type
     * @return mixed|null
     */
    public function getComparableValue(string $type);
}
