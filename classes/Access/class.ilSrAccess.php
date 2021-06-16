<?php

/**
 * Class ilSrAccess is responsible for all access checks.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class should ONLY contain PUBLIC STATIC methods, since access-checks
 * are needed within the whole codebase. Dependencies should therefore be
 * passed as parameters, so the method itself only depends on it's arguments.
 */
class ilSrAccess
{
    /**
     * test method
     *
     * @param int $user_id
     * @return bool
     */
    public static function canUserDoStuff(int $user_id) : bool
    {
        return true;
    }
}