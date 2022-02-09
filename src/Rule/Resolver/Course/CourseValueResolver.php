<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course\ICourseAware;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\IValueResolver;

/**
 * Class CourseValueResolver
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @package srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course
 */
final class CourseValueResolver implements IValueResolver
{
    /**
     * @var string resolver value type
     */
    public const VALUE_TYPE = 'course';

    /**
     * supported attributes
     */
    private const ATTRIBUTE_ID       = 'id';
    private const ATTRIBUTE_TITLE    = 'title';
    private const ATTRIBUTE_ACTIVE   = 'active';
    private const ATTRIBUTE_START    = 'start_date';
    private const ATTRIBUTE_END      = 'end_date';

    /**
     * returns the value for the given attribute from the given course object.
     *
     * @param \ilObjCourse $course
     * @param string       $attribute
     * @return bool|\ilDateTime|int|string|null
     */
    public function resolveCourseAttribute(\ilObjCourse $course, string $attribute)
    {
        switch ($attribute) {
            case self::ATTRIBUTE_ID:
                return $course->getId();
            case self::ATTRIBUTE_TITLE:
                return $course->getTitle();
            case self::ATTRIBUTE_ACTIVE:
                return $course->isActivated();
            case self::ATTRIBUTE_START:
                return $course->getCourseStart();
            case self::ATTRIBUTE_END:
                return $course->getCourseEnd();

            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function resolveLhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof ICourseAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not course-aware.");
        }

        return $this->resolveCourseAttribute(
            $comparison->getCourse(),
            $comparison->getRule()->getLhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function resolveRhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof ICourseAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not course-aware.");
        }

        return $this->resolveCourseAttribute(
            $comparison->getCourse(),
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
            self::ATTRIBUTE_TITLE,
            self::ATTRIBUTE_ACTIVE,
            self::ATTRIBUTE_START,
            self::ATTRIBUTE_END,
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