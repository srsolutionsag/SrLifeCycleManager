<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\CourseRessource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseAttributeFactory implements IDynamicAttributeProvider
{
    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return CourseAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [
            CourseActive::class,
            CourseStart::class,
            CourseEnd::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute
    {
        if (!$ressource instanceof CourseRessource) {
            return new CommonNull();
        }

        switch ($value) {
            case CourseStart::class:
                return new CourseStart($ressource->getCourse());
            case CourseEnd::class:
                return new CourseEnd($ressource->getCourse());
            case CourseActive::class:
                return new CourseActive($ressource->getCourse());

            default:
                return new CommonNull();
        }
    }
}