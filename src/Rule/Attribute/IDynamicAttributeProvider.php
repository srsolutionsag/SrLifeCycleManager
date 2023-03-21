<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDynamicAttributeProvider extends IAttributeValueProvider
{
    /**
     * Must return the according attribute instance for the give value.
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute;
}
