<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Taxonomy;

/**
 * Interface ITaxonomyAware
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface indicates that a @see IComparison is taxonomy-aware and could
 * contain dynamic attributes which must be resolved by @see TaxonomyValueResolver.
 *
 * Therefore comparisons that implement this interface must provide a method
 * that returns the object (that supports taxonomies) of the current comparison.
 */
interface ITaxonomyAware
{
    /**
     * returns the object of the current comparison.
     *
     * @return \ilObject
     */
    public function getObject() : \ilObject;
}