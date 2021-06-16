<?php

namespace srag\Plugins\SrCourseManager\Rule\Resolver\User;

use srag\Plugins\SrCourseManager\Rule\Comparison\IComparison;
use srag\Plugins\SrCourseManager\Rule\Resolver\IValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\User\IUserAware;

/**
 * Class UserValueResolver
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package srag\Plugins\SrCourseManager\Rule\Resolver\User
 */
final class UserValueResolver implements IValueResolver
{
    /**
     * @var string resolver value type
     */
    public const VALUE_TYPE = 'user';

    /**
     * resolvable attributes
     */
    private const ATTRIBUTE_ID          = 'id';
    private const ATTRIBUTE_COUNTRY     = 'country';
    private const ATTRIBUTE_CITY        = 'city';
    private const ATTRIBUTE_INSTITUTION = 'institution';

    /**
     * returns the value for the given attribute from the given user object.
     *
     * @param \ilObjUser $user
     * @param string     $attribute
     * @return int|null
     */
    public function resolveUserAttribute(\ilObjUser $user, string $attribute)
    {
        switch ($attribute) {
            case self::ATTRIBUTE_ID:
                return $user->getId();
            case self::ATTRIBUTE_COUNTRY:
                return $user->getSelectedCountry();
            case self::ATTRIBUTE_CITY:
                return $user->getCity();
            case self::ATTRIBUTE_INSTITUTION:
                return $user->getInstitution();

            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function resolveLhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof IUserAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not user-aware.");
        }

        return $this->resolveUserAttribute(
            $comparison->getUser(),
            $comparison->getRule()->getLhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function resolveRhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof IUserAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not user-aware.");
        }

        return $this->resolveUserAttribute(
            $comparison->getUser(),
            $comparison->getRule()->getRhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return [
            self::ATTRIBUTE_ID,
            self::ATTRIBUTE_INSTITUTION,
            self::ATTRIBUTE_CITY,
            self::ATTRIBUTE_COUNTRY,
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateValue($value) : bool
    {
        return in_array($value, $this->getAttributes());
    }
}