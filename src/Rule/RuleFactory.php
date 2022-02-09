<?php

namespace srag\Plugins\SrLifeCycleManager\Rule;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\ResolverFactory;

/**
 * Class Factory
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFactory
{
    /**
     * @var ComparisonFactory
     */
    private $comparisons;

    /**
     * @var ResolverFactory
     */
    private $resolver;

    /**
     * @param ComparisonFactory $comparisons
     * @param ResolverFactory   $resolvers
     */
    private function __construct(ComparisonFactory $comparisons, ResolverFactory $resolvers)
    {
        $this->comparisons = $comparisons;
        $this->resolver    = $resolvers;
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