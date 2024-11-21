<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

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