<?php

namespace srag\Plugins\SrCourseManager\Rule\Resolver;

use srag\Plugins\SrCourseManager\Rule\Resolver\User\UserValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\Course\CourseValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\Common\CommonValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\Common\NullValueResolver;
use srag\Plugins\SrCourseManager\Rule\Resolver\Taxonomy\TaxonomyValueResolver;

/**
 * Class ResolverFactory
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ResolverFactory
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var IValueResolver[]
     */
    private $resolvers = [];

    /**
     * prevents multiple instances
     */
    private function __wakeup() {}
    private function __clone() {}

    /**
     * ResolverFactory constructor.
     */
    private function __construct()
    {
        $this->resolvers[CommonValueResolver::class]    = new CommonValueResolver();
        $this->resolvers[TaxonomyValueResolver::class]  = new TaxonomyValueResolver();
        $this->resolvers[CourseValueResolver::class]    = new CourseValueResolver();
        $this->resolvers[UserValueResolver::class]      = new UserValueResolver();
        $this->resolvers[NullValueResolver::class]      = new NullValueResolver();
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (!isset(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    /**
     * returns the corresponding value-resolver for the given value-type.
     *
     * @param string $type
     * @return IValueResolver
     */
    public function getResolverForType(string $type) : IValueResolver
    {
        if (in_array($type, CommonValueResolver::VALUE_TYPES)) {
            return $this->common();
        }

        // if $type contains the taxonomy-resolver value type (in order to work with pre- and postfixes)
        if (preg_match('/' . TaxonomyValueResolver::VALUE_TYPE . '/', $type)) {
            return $this->taxonomy();
        }

        switch ($type) {
            case CourseValueResolver::VALUE_TYPE:
                return $this->course();
            case UserValueResolver::VALUE_TYPE:
                return $this->user();

            default:
                return $this->null();
        }
    }

    /**
     * @return CommonValueResolver
     */
    public function common() : CommonValueResolver
    {
        return $this->resolvers[CommonValueResolver::class];
    }

    /**
     * @return CourseValueResolver
     */
    public function course() : CourseValueResolver
    {
        return $this->resolvers[CourseValueResolver::class];
    }

    /**
     * @return TaxonomyValueResolver
     */
    public function taxonomy() : TaxonomyValueResolver
    {
        return $this->resolvers[TaxonomyValueResolver::class];
    }

    /**
     * @return UserValueResolver
     */
    public function user() : UserValueResolver
    {
        return $this->resolvers[UserValueResolver::class];
    }

    /**
     * @return NullValueResolver
     */
    public function null() : NullValueResolver
    {
        return $this->resolvers[NullValueResolver::class];
    }
}