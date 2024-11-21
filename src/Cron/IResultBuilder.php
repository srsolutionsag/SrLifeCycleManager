<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Cron;

use ilCronJobResult;

/**
 * Describes a cron-job result builder.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IResultBuilder
{
    /**
     * Requests a new instance of a cron-job result.
     *
     * @return IResultBuilder
     */
    public function request() : IResultBuilder;

    /**
     * Returns the currently built cron-job result.
     *
     * @return ilCronJobResult
     */
    public function getResult() : ilCronJobResult;
}