<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfig
{
    /**
     * @var string config primary key for privileged role ids. privileged roles are allowed
     *             to create and update their own routines.
     */
    public const CNF_PRIVILEGED_ROLES = 'cnf_privileged_roles';

    /**
     * @var string config primary key that enables or disables the creation of routines in the
     *             tool e.g. the repository.
     */
    public const CNF_CREATE_ROUTINES = 'cnf_can_tool_create_routines';

    /**
     * @var string config primary key that enables or disables the visibility of routines in the
     *             tool e.g. the repository.
     */
    public const CNF_SHOW_ROUTINES = 'cnf_can_tool_show_routines';

    /**
     * @return int[]
     */
    public function getPrivilegedRoles() : array;

    /**
     * @param int[] $privileged_roles
     */
    public function setPrivilegedRoles(array $privileged_roles) : IConfig;

    /**
     * @return bool
     */
    public function canToolShowRoutines() : bool;

    /**
     * @param bool $can_show
     */
    public function setToolCanShowRoutines(bool $can_show) : IConfig;

    /**
     * @return bool
     */
    public function canToolCreateRoutines() : bool;

    /**
     * @param bool $can_create
     */
    public function setToolCanCreateRoutines(bool $can_create) : IConfig;
}