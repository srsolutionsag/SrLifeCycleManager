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
     * @var string config primary key that determines if routines can be created in the repository.
     *             If enabled, the tool will show an entry for privileged roles.
     */
    public const CNF_CREATE_ROUTINES_IN_REPOSITORY = 'cnf_create_routines_in_repository';

    /**
     * @var string config primary key that determines if active routines should be displayed in the
     *             repository. If enabled, the tool will show a table that lists all routines that
     *             affect the current object.
     */
    public const CNF_SHOW_ROUTINES_IN_REPOSITORY = 'cnf_show_routines_in_repository';

    // IConfig attribute names:
    public const F_IDENTIFIER = 'identifier';
    public const F_CONFIG = 'configuration';

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
    public function showRoutinesInRepository() : bool;

    /**
     * @param bool $can_show
     */
    public function setShowRoutinesInRepository(bool $can_show) : IConfig;

    /**
     * @return bool
     */
    public function createRoutinesInRepository() : bool;

    /**
     * @param bool $can_create
     */
    public function setCreateRoutinesInRepository(bool $can_create) : IConfig;
}