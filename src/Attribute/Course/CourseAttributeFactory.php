<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilObjCourse;
use srag\Plugins\SrLifeCycleManager\Dependency\DependencyPool;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseAttributeFactory
{
    /**
     * @param DependencyPool $pool
     * @param string         $value
     * @return IAttribute
     */
    public function getAttribute(DependencyPool $pool, string $value) : IAttribute
    {
        switch ($value) {
            case CourseStart::class:
                return new CourseStart($pool->getCourse());

            case CourseEnd::class:
                return new CourseEnd($pool->getCourse());

            case CourseTitle::class:
                return new CourseTitle($pool->getCourse());

            case CourseMetadata::class:
                return new CourseMetadata(
                    $pool->getDatabase(),
                    $pool->getCourse()
                );

            case CourseTaxonomy::class:
                return new CourseTaxonomy(
                    $pool->getDatabase(),
                    $pool->getCourse()
                );

            default:
                return new NullAttribute($value);
        }
    }
}