<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Config implements IConfig
{
    /**
     * @var array
     */
    protected $privileged_roles = [];

    /**
     * @var bool
     */
    protected $move_to_bin = false;

    /**
     * @var bool
     */
    protected $show_in_repository = false;

    /**
     * @var bool
     */
    protected $create_in_repository = false;

    /**
     * @return array
     */
    public function getPrivilegedRoles() : array
    {
        return $this->privileged_roles;
    }

    /**
     * @param array $privileged_roles
     */
    public function setPrivilegedRoles(array $privileged_roles) : void
    {
        $this->privileged_roles = $privileged_roles;
    }

    /**
     * @return bool
     */
    public function shouldMoveToBin() : bool
    {
        return $this->move_to_bin;
    }

    /**
     * @param bool $move_to_bin
     */
    public function setMoveToBin(bool $move_to_bin) : void
    {
        $this->move_to_bin = $move_to_bin;
    }

    /**
     * @return bool
     */
    public function showRoutinesInRepository() : bool
    {
        return $this->show_in_repository;
    }

    /**
     * @param bool $show_in_repository
     */
    public function setShowRoutinesInRepository(bool $show_in_repository) : void
    {
        $this->show_in_repository = $show_in_repository;
    }

    /**
     * @return bool
     */
    public function createRoutinesInRepository() : bool
    {
        return $this->create_in_repository;
    }

    /**
     * @param bool $create_in_repository
     */
    public function setCreateRoutinesInRepository(bool $create_in_repository) : void
    {
        $this->create_in_repository = $create_in_repository;
    }
}