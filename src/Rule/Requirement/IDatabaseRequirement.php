<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement;

use ilDBInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDatabaseRequirement
{
    /**
     * @return ilDBInterface
     */
    public function getDatabase() : ilDBInterface;
}