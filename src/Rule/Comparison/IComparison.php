<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IComparison
{
    /**
     * @return bool
     */
    public function isApplicable() : bool;
}