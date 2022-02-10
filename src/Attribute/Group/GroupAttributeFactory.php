<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilObjCourse;
use srag\Plugins\SrLifeCycleManager\Dependency\DependencyPool;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupAttributeFactory
{
    /**
     * @param DependencyPool $pool
     * @param string         $value
     * @return IAttribute
     */
    public function getAttribute(DependencyPool $pool, string $value) : IAttribute
    {
        switch ($value) {
            case GroupTitle::class:
                return new GroupTitle($pool->getGroup());

            case GroupMetadata::class:
                return new GroupMetadata(
                    $pool->getDatabase(),
                    $pool->getGroup()
                );

            case GroupTaxonomy::class:
                return new GroupTaxonomy(
                    $pool->getDatabase(),
                    $pool->getGroup()
                );

            default:
                return new NullAttribute($value);
        }
    }
}