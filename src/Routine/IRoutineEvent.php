<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * This interface is used like an enum to define the possible events
 *
 * Please don't change the values of the constants, as they are used in the
 * database. If you need to do so, an according database update-step is required.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This can be refactored to an enum when PHP >= 8 is supported.
 */
interface IRoutineEvent
{
    public const EVENT_POSTPONE = 'routine_postpone';
    public const EVENT_OPT_OUT = 'routine_opt_out';
    public const EVENT_DELETE = 'routine_delete';
}
