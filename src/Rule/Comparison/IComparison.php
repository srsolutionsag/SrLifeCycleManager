<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

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