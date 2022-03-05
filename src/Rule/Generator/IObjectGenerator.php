<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Generator;

use ilObject;
use Iterator;

/**
 * Describes a generator that delivers repository objects.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IObjectGenerator extends Iterator
{
    /**
     * Yields a repository object or null if finished.
     *
     * @return ilObject|null
     */
    public function current() : ?ilObject;
}