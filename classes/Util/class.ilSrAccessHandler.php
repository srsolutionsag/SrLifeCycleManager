<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use ILIAS\DI\RBACServices;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;

/**
 * This class is responsible for all access-checks.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * All methods execute access-checks for the current user (the user this
 * helper got initialized with), therefore the naming is always in regard
 * to the current user: e.g. isAdministrator() or isOwnerOf(id).
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrAccessHandler
{
    public function __construct(
        protected RBACServices $access,
        protected IGeneralRepository $general_repository,
        protected IConfig $config,
        protected \ilObjUser $user
    ) {
    }

    /**
     * Checks if the current user is assigned the global administrator role.
     */
    public function isAdministrator(): bool
    {
        return $this->access->review()->isAssigned(
            $this->user->getId(),
            (int) SYSTEM_ROLE_ID
        );
    }

    /**
     * Checks if the current user is assigned one of the configured roles that
     * are privileged to manage routines.
     */
    public function canManageRoutines(): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->access->review()->isAssignedToAtLeastOneGivenRole(
            $this->user->getId(),
            $this->config->getManageRoutineRoles()
        );
    }

    /**
     * Checks if the current use is privileged to manage assignments between
     * routines and objects.
     */
    public function canManageAssignments(): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->access->review()->isAssignedToAtLeastOneGivenRole(
            $this->user->getId(),
            $this->config->getManageAssignmentRoles()
        );
    }

    /**
     * Checks if the current user is administrator of the given object.
     * This will only return true, if the object supports participants.
     */
    public function isAdministratorOf(ilObject $object): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if (null === ($participants = $this->general_repository->getParticipantObject($object))) {
            return false;
        }

        // participants->getAdmins() will return a string array.
        return in_array($this->user->getId(), $participants->getAdmins(), false);
    }

    /**
     * Checks if the current user is not logged in (anonymous).
     */
    public function isAnonymous(): bool
    {
        return (ANONYMOUS_USER_ID === $this->user->getId());
    }

    /**
     * Checks if the given user id matches the current user id.
     */
    public function isCurrentUser(int $user_id): bool
    {
        return ($user_id === $this->user->getId());
    }
}
