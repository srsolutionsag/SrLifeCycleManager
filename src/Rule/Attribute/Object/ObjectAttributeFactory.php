<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IObjectRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectAttributeFactory implements IDynamicAttributeProvider
{
    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return ObjectAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [
            ObjectAge::class,
            ObjectCreation::class,
            ObjectMetadata::class,
            ObjectTaxonomy::class,
            ObjectTitle::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute
    {
        if (!$ressource instanceof IObjectRessource) {
            return new CommonNull();
        }

        switch ($value) {
            case ObjectAge::class:
                return new ObjectAge($ressource->getObject());
            case ObjectCreation::class:
                return new ObjectCreation($ressource->getObject());
            case ObjectMetadata::class:
                return new ObjectMetadata($ressource->getDatabase(), $ressource->getObject());
            case ObjectTaxonomy::class:
                return new ObjectTaxonomy($ressource->getDatabase(), $ressource->getObject());
            case ObjectTitle::class:
                return new ObjectTitle($ressource->getObject());

            default:
                return new CommonNull();
        }
    }
}
