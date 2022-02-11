<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IDatabaseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGroupRequirement extends IRequirement, IDatabaseRequirement
{
    /**
     * @return ilObjGroup
     */
    public function getGroup() : ilObjGroup;
}