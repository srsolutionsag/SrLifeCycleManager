<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Generator;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDeletableObject
{
    /**
     * Returns the object that should be deleted.
     *
     * @return ilObject
     */
    public function getInstance() : ilObject;

    /**
     * Returns routines that made this object deletable.
     *
     * @return IRoutine[]
     */
    public function getAffectedRoutines() : array;
}