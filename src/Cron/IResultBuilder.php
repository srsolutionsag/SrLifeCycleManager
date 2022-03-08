<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

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