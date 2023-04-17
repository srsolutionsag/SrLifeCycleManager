<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupAttributeFactory implements IDynamicAttributeProvider
{
    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return GroupAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute
    {
        return new CommonNull();
    }
}
