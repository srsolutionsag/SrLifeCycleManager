<?php

namespace srag\Plugins\SrCourseManager\Rule;

use srag\Plugins\SrCourseManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrCourseManager\Rule\Resolver\ResolverFactory;

/**
 * Class Factory
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class Factory
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var ComparisonFactory
     */
    private $comparisons;

    /**
     * @var ResolverFactory
     */
    private $resolver;

    /**
     * prevents multiple instances
     */
    private function __wakeup() {}
    private function __clone() {}

    /**
     * Factory constructor
     */
    private function __construct()
    {
        $this->comparisons = ComparisonFactory::getInstance();
        $this->resolver    = ResolverFactory::getInstance();
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
     * @return ComparisonFactory
     */
    public function comparison() : ComparisonFactory
    {
        return $this->comparisons;
    }

    /**
     * @return ResolverFactory
     */
    public function resolver() : ResolverFactory
    {
        return $this->resolver;
    }
}