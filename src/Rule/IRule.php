<?php

namespace srag\Plugins\SrCourseManager\Rule;

/**
 * Interface IRule
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface definces what a rule must look like.
 *
 * The implementation should be either as DTO or AR, depending
 * on how the database access is handled.
 */
interface IRule
{
    public const OPERATOR_EQUAL         = '=';
    public const OPERATOR_NOT_EQUAL     = '!=';
    public const OPERATOR_GREATER       = '>';
    public const OPERATOR_GREATER_EQUAL = '>=';
    public const OPERATOR_LESSER        = '<';
    public const OPERATOR_LESSER_EQUAL  = '<=';
    public const OPERATOR_CONTAINS      = 'in_value';
    public const OPERATOR_IN_ARRAY      = 'in_array';

    /**
     * @var string[] possible operators
     */
    public const OPERATORS = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_GREATER,
        self::OPERATOR_GREATER_EQUAL,
        self::OPERATOR_LESSER,
        self::OPERATOR_LESSER_EQUAL,
        self::OPERATOR_CONTAINS,
        self::OPERATOR_IN_ARRAY,
    ];

    public const OPERATOR_STRING_EQUAL            = 'operator_equal';
    public const OPERATOR_STRING_NOT_EQUAL        = 'operator_not_equal';
    public const OPERATOR_STRING_GREATER          = 'operator_greater';
    public const OPERATOR_STRING_GREATER_EQUAL    = 'operator_greater_equal';
    public const OPERATOR_STRING_LESSER           = 'operator_lesser';
    public const OPERATOR_STRING_LESSER_EQUAL     = 'operator_lesser_equal';
    public const OPERATOR_STRING_CONTAINS         = 'operator_in_value';
    public const OPERATOR_STRING_IN_ARRAY         = 'operator_in_array';

    /**
     * @var string[] possible operators as string
     */
    public const OPERATOR_STRINGS = [
        self::OPERATOR_STRING_EQUAL,
        self::OPERATOR_STRING_NOT_EQUAL,
        self::OPERATOR_STRING_GREATER,
        self::OPERATOR_STRING_GREATER_EQUAL,
        self::OPERATOR_STRING_LESSER,
        self::OPERATOR_STRING_LESSER_EQUAL,
        self::OPERATOR_STRING_CONTAINS,
        self::OPERATOR_STRING_IN_ARRAY,
    ];

    /**
     * @return int|null
     */
    public function getId() : ?int;

    /**
     * @param int|null $id
     * @return IRule
     */
    public function setId(?int $id) : IRule;

    /**
     * @return string
     */
    public function getLhsType() : string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setLhsType(string $type) : IRule;

    /**
     * @return mixed
     */
    public function getLhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setLhsValue($value) : IRule;

    /**
     * @return string
     */
    public function getOperator() : string;

    /**
     * @param string $operator
     * @return IRule
     */
    public function setOperator(string $operator) : IRule;

    /**
     * @return string
     */
    public function getRhsType() : string;

    /**
     * @param string $type
     * @return IRule
     */
    public function setRhsType(string $type) : IRule;

    /**
     * @return mixed
     */
    public function getRhsValue();

    /**
     * @param $value
     * @return IRule
     */
    public function setRhsValue($value) : IRule;
}