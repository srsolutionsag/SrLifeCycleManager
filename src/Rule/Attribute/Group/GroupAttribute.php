<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class GroupAttribute implements IAttribute
{
    /**
     * @var \ilObjGroup
     */
    protected $group;

    public function __construct(\ilObjGroup $group)
    {
        $this->group = $group;
    }
}
