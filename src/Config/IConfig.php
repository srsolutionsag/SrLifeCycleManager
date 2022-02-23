<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfig
{
    /**
     * @return array
     */
    public function getPrivilegedRoles() : array;

    /**
     * @param array $privileged_roles
     */
    public function setPrivilegedRoles(array $privileged_roles) : void;

    /**
     * @return bool
     */
    public function shouldMoveToBin() : bool;

    /**
     * @param bool $move_to_bin
     */
    public function setMoveToBin(bool $move_to_bin) : void;

    /**
     * @return bool
     */
    public function showRoutinesInRepository() : bool;

    /**
     * @param bool $show_in_repository
     */
    public function setShowRoutinesInRepository(bool $show_in_repository) : void;

    /**
     * @return bool
     */
    public function createRoutinesInRepository() : bool;

    /**
     * @param bool $create_in_repository
     */
    public function setCreateRoutinesInRepository(bool $create_in_repository) : void;
}