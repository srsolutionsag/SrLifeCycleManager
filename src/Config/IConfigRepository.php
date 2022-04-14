<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * Describes the CRUD operations of the configuration.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfigRepository
{
    /**
     * Fetches the configurations from the database.
     *
     * @return IConfig
     */
    public function get() : IConfig;

    /**
     * Creates or updates the given configurations in the database.
     *
     * @param IConfig $config
     * @return IConfig
     */
    public function store(IConfig $config) : IConfig;
}