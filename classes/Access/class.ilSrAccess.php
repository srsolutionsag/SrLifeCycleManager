<?php

/**
 * Class ilSrAccess is responsible for all access checks.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class should ONLY contain PUBLIC STATIC methods, since access-checks
 * are needed within the whole codebase. Dependencies should therefore be either
 * passed as parameters or from the global DI container $DIC.
 */
class ilSrAccess
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

        return $DIC->rbac()->review()->isAssigned($user_id, SYSTEM_ROLE_ID);
    }

    /**
     * test method, returns always true and MUST be deleted on plugin release.
     *
     * @param int|null $user_id
     * @return bool
     */
    public static function canUserDoStuff(int $user_id = null) : bool
    {
        return true;
    }
}