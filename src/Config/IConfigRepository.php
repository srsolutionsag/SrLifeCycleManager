<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfigRepository
{
    /**
     * Returns the latest configuration object.
     *
     * @return IConfig
     */
    public function get() : IConfig;

    /**
     * Stores the configuration from given identifier => value pairs.
     *
     * @param array<string, mixed> $post_data
     * @return IConfig
     */
    public function store(array $post_data) : IConfig;
}