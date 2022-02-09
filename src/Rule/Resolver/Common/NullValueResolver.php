<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\IValueResolver;

/**
 * Class NullValueResolver
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @package srag\Plugins\SrLifeCycleManager\Rule\Resolver\Common
 */
final class NullValueResolver implements IValueResolver
{
    /**
     * @var string resolver value type
     */
    public const VALUE_TYPE = 'null';

    /**
     * @inheritDoc
     */
    public function resolveLhsValue(IComparison $comparison)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function resolveRhsValue(IComparison $comparison)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function validateValue($value) : bool
    {
        return (null === $value);
    }
}