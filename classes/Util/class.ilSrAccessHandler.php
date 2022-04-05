<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use ILIAS\DI\RBACServices;

/**
 * This class is responsible for all access-checks.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * All methods execute access-checks for the current user (the user this
 * helper got initialized with), therefore the naming is always in regard
 * to the current user: e.g. isAdministrator() or isOwnerOf(id).
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrAccessHandler
{
    /**
     * @var RBACServices
     */
    protected $access;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @param RBACServices $access
     * @param IConfig      $config
     * @param ilObjUser    $user
     */
    public function __construct(
        RBACServices $access,
        IConfig $config,
        ilObjUser $user
    ) {
        $this->access = $access;
        $this->config = $config;
        $this->user = $user;
    }

    /**
     * Checks if the current user is assigned the global administrator role.
     *
     * @return bool
     */
    public function isAdministrator() : bool
    {
        return $this->access->review()->isAssigned(
            $this->user->getId(),
            (int) SYSTEM_ROLE_ID
        );
    }

    /**
     * Checks if the current user can view routines. If no object is provided, the
     * user must be assigned a privileged role.
     *
     * @param int|null $ref_id
     * @return bool
     */
    public function canViewRoutines(int $ref_id = null) : bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if (null !== $ref_id) {
            return (
                $this->canManageRoutines() ||
                $this->isAdministratorOf($ref_id)
            );
        }

        return $this->canManageRoutines();
    }

    /**
     * Checks if the current user is assigned one of the configured roles that
     * are privileged to manage routines.
     *
     * @return bool
     */
    public function canManageRoutines() : bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->access->review()->isAssignedToAtLeastOneGivenRole(
            $this->user->getId(),
            $this->config->getPrivilegedRoles()
        );
    }

    /**
     * Checks if the current use is privileged to manage assignments between
     * routines and objects.
     *
     * @todo: this is currently just an adapter for canManageRoutines(), but
     *        we might introduce a separate configuration for this.
     *
     * @return bool
     */
    public function canManageAssignments() : bool
    {
        return $this->canManageRoutines();
    }

    /**
     * Checks if the current user is administrator of the given object (ref-id).
     *
     * @param int $ref_id
     * @return bool
     */
    public function isAdministratorOf(int $ref_id) : bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        try {
            $participants = ilParticipants::getInstance($ref_id);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return in_array(
            $this->user->getId(),
            $participants->getAdmins(),
            true
        );
    }

    /**
     * Checks if the given user id matches the current user id.
     *
     * @param int $user_id
     * @return bool
     */
    public function isCurrentUser(int $user_id) : bool
    {
        return ($user_id === $this->user->getId());
    }
}