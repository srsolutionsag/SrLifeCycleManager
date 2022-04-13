<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine\Provider;

use Iterator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDeletableObjectProvider extends Iterator
{
    /**
     * Returns the next valid deletable object.
     *
     * Valid means, that at least one active routine exists, that affects
     * the object and all rules are applicable.
     *
     * @return IDeletableObject|null
     */
    public function current() : ?IDeletableObject;
}