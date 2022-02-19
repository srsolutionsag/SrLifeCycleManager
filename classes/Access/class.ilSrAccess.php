<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * Class ilSrAccess is responsible for all access checks.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This class should ONLY contain PUBLIC STATIC methods, since access-checks
 * are needed within the whole codebase. Dependencies should therefore be either
 * passed as parameters or from the global DI container $DIC.
 */
final class ilSrAccess
{
    /**
     * checks if a user for given id is assigned to the administrator role.
     *
     * @param int $user_id
     * @return bool
     */
    public static function isUserAdministrator(int $user_id) : bool
    {
        global $DIC;

        return $DIC->rbac()->review()->isAssigned($user_id, (int) SYSTEM_ROLE_ID);
    }

    /**
     * checks if a user for given id is eligible to manage routines by checking
     * assigned roles for administrator or configured global roles.
     *
     * @see IConfig::CNF_GLOBAL_ROLES
     *
     * @param int $user_id
     * @return bool
     */
    public static function isUserAssignedToConfiguredRole(int $user_id) : bool
    {
        global $DIC;

        /** @var $config IConfig */
        $config = ilSrConfig::find(IConfig::CNF_GLOBAL_ROLES);
        if (null !== $config) {
            return $DIC->rbac()->review()->isAssignedToAtLeastOneGivenRole($user_id, $config->getValue());
        }

        return false;
    }
}