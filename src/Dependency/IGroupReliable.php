<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Dependency;

use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGroupReliable
{
    /**
     * @return ilObjGroup
     */
    public function getGroup() : ilObjGroup;
}